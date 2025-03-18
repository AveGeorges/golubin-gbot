from pyrogram.types import Message

from src.database.models import TelegramLink
from src.database.operations import (get_keyword_rows, )


async def find_message_categories(text: str, telegram_link: TelegramLink) -> set:
    keyword_rows = await get_keyword_rows(telegram_link.id)

    # negative_keyword_rows = await get_negative_keyword_rows()
    categories = set()

    text_lowercase = text.lower()

    for keyword_row in keyword_rows:
        if keyword_row.keyword in text_lowercase:
            # if not any(((negative_keyword_row.keyword in text_lowercase) for negative_keyword_row in negative_keyword_rows))
            categories.add(keyword_row.category.id)

    return categories


def extract_chat(telegram_link: TelegramLink):
    if telegram_link.chat_id:
        return int(telegram_link.chat_id)

    link = telegram_link.link
    if telegram_link.is_private:
        return link  # Для закрытых групп возвращаем ссылку как есть
    else:
        if link and "+" not in link:  # Для открытых групп удаляем префикс "t.me/"
            link = link.removeprefix("t.me/")
        return link


def get_text_with_link(message: Message):
    text = message.text
    title = message.chat.title
    link = message.link

    text += f'\n\n<a href="{link}">Переслано из {title}</a>'
    if message.from_user and message.from_user.username:
        text += f'\n\n👉 @{message.from_user.username}'
    return text
