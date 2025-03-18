from dataclasses import dataclass, field
from datetime import datetime, timedelta
from typing import Optional, AsyncGenerator
import asyncio
import uvloop
from pyrogram import Client
from pyrogram.errors import UsernameInvalid, FloodWait, UsernameNotOccupied, BadRequest, ChannelInvalid
from pyrogram.types import Message
import traceback

from src.config import settings
from src.database.models import TelegramLink
from src.database.operations import (
    get_tg_links,
    save_message,
    check_duplicate,
    save_chat_id,
    set_telegram_link_invalid,
    update_telegram_link_last_check,
    set_telegram_link_chat_id_null
)
from src.helpers import extract_chat, find_message_categories, get_text_with_link
from src.logger import admin_logger, bot_logger


@dataclass
class ParserConfig:
    """Configuration settings for the parser"""
    MAX_MESSAGES_PER_CHAT: int = 3000
    MESSAGE_LOG_INTERVAL: int = 500

@dataclass
class ClientConfig:
    """Configuration for a single Telegram client"""
    session_name: str
    api_id: int
    api_hash: str


@dataclass
class ParserStats:
    """Statistics tracking for parser operation"""
    session_name: str
    exclude_unknown_chats: bool
    message_count: int = 0
    chats_count: int = 0
    relevant_messages: int = 0
    start_time: datetime = field(default_factory=datetime.now)

    def log_starting(self, chats_count: int):
        """Log current starting process"""
        bot_logger.info(
            f"Get chats "
            f"Session {self.session_name}: "
            f"{self.exclude_unknown_chats=}, "
            f"Всего чатов: {chats_count} "
        )

    def log_progress(self, messages_in_chat: int, current_chat: str):
        """Log current parsing progress"""
        if self.message_count % ParserConfig.MESSAGE_LOG_INTERVAL == 0:
            bot_logger.info(
                f"Session {self.session_name}: "
                f"{self.exclude_unknown_chats=}, "
                f"{self.message_count=}, {messages_in_chat=}, "
                f"{self.chats_count=}, {self.relevant_messages=}, "
                f"current_chat={current_chat}"
            )

    def log_final_stats(self):
        """Log final parsing statistics"""
        duration_minutes = (datetime.now() - self.start_time).total_seconds() / 60
        bot_logger.info(
            f"Session {self.session_name} - "
            f"{self.exclude_unknown_chats=}, "
            f"Processed: {self.message_count=}, {self.chats_count=}, "
            f"{self.relevant_messages=}, {duration_minutes:.2f} min"
        )


class TelegramParser:
   def __init__(self, client: Client, session_name: str):
      self.client = client
      self.config = ParserConfig()
      self.session_name = session_name

   async def process_message(
         self,
         message: Message,
         chat_id: int,
         stats: ParserStats,
         telegram_link: TelegramLink,
   ) -> Optional[int]:
      """Process a single message and return its ID if relevant"""
      if not message.text:
         return None

      if message.date < datetime.utcnow() - timedelta(days=1):
         return None

      categories = await find_message_categories(message.text, telegram_link)

      if not (categories and await check_duplicate(message.text)):
         return message.id

      try:
         text_with_link = get_text_with_link(message)
      except Exception as e:
         bot_logger.error(f"Failed to get text with link:\n {e}")
         return message.id

      await self._save_relevant_message(message, text_with_link, categories)
      stats.relevant_messages += 1

      return message.id

   async def _save_relevant_message(
         self,
         message: Message,
         text_with_link: str,
         categories: list
   ):
      """Save a relevant message to the database"""
      await save_message(
         text=text_with_link,
         date=message.date,
         categories=categories,
         from_user_id=message.from_user.id if message.from_user else None,
         from_username=message.from_user.username if message.from_user else None,
         chat_id=message.chat.id,
         link=message.link,
      )
      admin_logger.info(f"{message.chat.id}: {text_with_link} <br/>")
      bot_logger.info(f"{message.chat.id}: {text_with_link}")

   async def process_chat(
         self,
         telegram_link: TelegramLink,
         stats: ParserStats
   ) -> None:
      """Process a single chat's messages"""
      chat = extract_chat(telegram_link)
      if not chat:
         return

      max_id = telegram_link.last_message_id or 0
      last_check_at = datetime.utcnow()
      messages_in_chat = 0

      try:
         async for message in self._get_chat_messages(chat, max_id):
               messages_in_chat += 1
               if messages_in_chat > self.config.MAX_MESSAGES_PER_CHAT:
                  break

               save_chat_id(telegram_link, message.chat.id, self.session_name)
               stats.message_count += 1
               stats.log_progress(messages_in_chat, telegram_link.link)

               new_max_id = await self.process_message(message, message.chat.id, stats, telegram_link)
               if new_max_id:
                  max_id = max(new_max_id, max_id)
                  telegram_link.last_check_at = datetime.utcnow()

      except (UsernameInvalid, UsernameNotOccupied):
         bot_logger.warning(f"UsernameInvalid, {telegram_link.link=}")
         set_telegram_link_invalid(telegram_link)
      except ChannelInvalid:
         bot_logger.warning(
               f"ChannelInvalid, {self.session_name=} {telegram_link.link=} {telegram_link.chat_id=} {chat=}")
         set_telegram_link_chat_id_null(telegram_link, self.session_name)
      except BadRequest:
         bot_logger.warning(f'BadRequest for chat: {chat}')
      except FloodWait as e:
         bot_logger.warning(f'FloodWait {e.value} seconds')
         await asyncio.sleep(e.value)
      except Exception as e:
         bot_logger.error(f"Unexpected error: {e}\n{traceback.format_exc()}", exc_info=True)
      finally:
         update_telegram_link_last_check(telegram_link, last_check_at, max_id)

   async def _get_chat_messages(
         self,
         chat: str,
         min_id: int
   ) -> AsyncGenerator[Message, None]:
      """Get messages from a chat with error handling"""
      async for message in self.client.get_chat_history(chat, min_id=min_id):
         yield message

   async def run(self, exclude_unknown_chats: bool = True, is_private: bool = False) -> None:
      """Main parser loop"""
      while True:
         stats = ParserStats(session_name=self.session_name, exclude_unknown_chats=exclude_unknown_chats)
         links = await get_tg_links(exclude_unknown_chats=exclude_unknown_chats, session_name=self.session_name, is_private=is_private)

         if not links:
               await asyncio.sleep(300)
         stats.log_starting(len(links))

         for telegram_link in links:
               stats.chats_count += 1
               await self.process_chat(telegram_link, stats)

         stats.log_final_stats()


class MultiClientParser:
   def __init__(self, client_configs: list[ClientConfig]):
      self.client_configs = client_configs

   async def run_client(self, config: ClientConfig) -> None:
      """Run a single client instance"""
      while True:
         bot_logger.info(f"Starting parser for session {config.session_name}")
         try:
               async with Client(
                  name=config.session_name,
                  api_id=config.api_id,
                  api_hash=config.api_hash
               ) as app:
                  parser = TelegramParser(app, config.session_name)
                  tasks = [
                     asyncio.create_task(parser.run(exclude_unknown_chats=False, is_private=False)),  # Парсер открытых групп
                     asyncio.create_task(parser.run(exclude_unknown_chats=True, is_private=False))   # Парсер открытых групп
                  ]
                  await asyncio.gather(*tasks)
         except OSError as e:
               bot_logger.error(f"OS Error in session {config.session_name}: {e}")
               await asyncio.sleep(5)
         except Exception as e:
               bot_logger.error(f"Unexpected error in session {config.session_name}: {e}")
               await asyncio.sleep(5)

   async def run_all(self) -> None:
      """Run all client instances simultaneously"""
      client_tasks = []
      for config in self.client_configs:
         task = asyncio.create_task(self.run_client(config))
         client_tasks.append(task)

      await asyncio.gather(*client_tasks)


async def main() -> None:
    # Конфигурация для разных клиентов
    client_configs = [
        ClientConfig(
            session_name=settings.session_name,
            api_id=settings.api_id,
            api_hash=settings.api_hash
        ),
        ClientConfig(
            session_name=settings.session_name_2,
            api_id=settings.api_id_2,
            api_hash=settings.api_hash_2
        ),
        ClientConfig(
            session_name=settings.session_name_3,
            api_id=settings.api_id_3,
            api_hash=settings.api_hash_3
        ),
        ClientConfig(
            session_name=settings.session_name_4,
            api_id=settings.api_id_4,
            api_hash=settings.api_hash_4
        ),
        ClientConfig(
            session_name=settings.session_name_5,
            api_id=settings.api_id_5,
            api_hash=settings.api_hash_5
        ),
        ClientConfig(
            session_name=settings.session_name_6,
            api_id=settings.api_id_6,
            api_hash=settings.api_hash_6
        ),
        ClientConfig(
            session_name=settings.session_name_7,
            api_id=settings.api_id_7,
            api_hash=settings.api_hash_7
        ),
    ]

    parser = MultiClientParser(client_configs)
    await parser.run_all()


if __name__ == "__main__":
    uvloop.install()
    asyncio.run(main())
