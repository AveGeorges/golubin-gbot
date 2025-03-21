from datetime import datetime, timedelta
from typing import Sequence, Optional
from uuid import uuid4

from async_lru import alru_cache
from bs4 import BeautifulSoup as bs
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from sqlalchemy import case, func
from sqlalchemy.orm import selectinload
from sqlmodel import Session, select, update, asc, or_, desc

from src.config import settings
from src.database.connection import engine, engine_prod
from src.database.models import Message, Keyword, TelegramLink, CategoryMessageLink, Category, CategoryTelegramLinkLink
from src.database.models_prod import NewMessages
from src.logger import bot_logger
from time import sleep


@alru_cache(ttl=300)
async def get_keyword_rows(telegram_link_id: str) -> Sequence[Keyword]:
    with Session(engine) as session:
        keyword_rows = session.exec(
            select(Keyword)
            .options(selectinload(Keyword.category))
            .join(Category)
            .join(CategoryTelegramLinkLink)
            .where(or_(CategoryTelegramLinkLink.telegram_link_id == telegram_link_id,
                   Category.global_search == 1))
            .group_by(Keyword.id)
        ).all()

    return keyword_rows


@alru_cache(ttl=300)
async def get_negative_keyword_rows() -> Sequence[Keyword]:
    with Session(engine) as session:
        keyword_rows = session.exec(select(NegativeKeyword).options(selectinload(NegativeKeyword.category))).all()

    return keyword_rows


async def save_message(
    text: str,
    date: datetime,
    categories: Sequence[int],
    from_user_id: Optional[int] = None,
    from_username: Optional[str] = None,
    chat_id: Optional[int] = None,
    link: Optional[str] = None
) -> None:
    with Session(engine) as session:
        message_id = uuid4()

        message_row = Message(id=message_id, text=text, date=date, from_user_id=from_user_id,
                              from_username=from_username, chat_id=chat_id, link=link)
        session.add(message_row)
        session.commit()
        session.bulk_insert_mappings(CategoryMessageLink,
                                     [{'category_id': category_id, 'message_id': message_id} for category_id in
                                      categories])
        session.commit()

    with Session(engine_prod) as session:
        for category in categories:
            session.add(NewMessages(category=category, text=text, date=date))
        session.commit()


async def get_tg_links(exclude_unknown_chats=True, session_name: Optional[str] = None, is_private: bool = False) -> Sequence[TelegramLink]:
    with Session(engine) as session:
        if exclude_unknown_chats:
            query = (
                select(TelegramLink)
                .where(
                    (TelegramLink.chat_id != None) == exclude_unknown_chats,
                    or_(TelegramLink.parser_account == session_name, TelegramLink.parser_account == None),
                    TelegramLink.is_private == is_private  # Фильтрация по is_private
                )
                .order_by(
                    case(
                        (TelegramLink.last_check_at == None, 1), else_=0
                    ),
                    asc(TelegramLink.last_check_at)
                )
                .limit(30)
            )
        else:
            query = (
                select(TelegramLink)
                .where(
                    TelegramLink.chat_id == None,
                    or_(TelegramLink.parser_account == session_name, TelegramLink.parser_account == None),
                    or_(TelegramLink.invalid == False, TelegramLink.invalid == None),
                    TelegramLink.is_private == is_private  # Фильтрация по is_private
                )
                .order_by(func.rand())
                .limit(1)
            )

        telegram_links = session.exec(query).all()
        return telegram_links


async def update_tg_link(link: TelegramLink) -> TelegramLink:
    with Session(engine) as session:
        session.add(link)
        session.commit()
        session.refresh(link)
        return link


async def check_duplicate(text: str) -> bool:
    is_not_duplicate = True

    if not text:
        return is_not_duplicate

    text = bs(text.split('<a href="')[0], "html.parser").text
    yesterday = datetime.utcnow() - timedelta(days=1)

    with Session(engine) as session:
        statement = select(Message).where(Message.created_at > yesterday)
        messages = session.exec(statement)

        for message in messages:

            sent_text = message.text
            sent_text = bs(sent_text.split('<a href="')[0], 'html.parser').text

            vectorizer = TfidfVectorizer()
            vectors = vectorizer.fit_transform([text, sent_text])
            similarity = cosine_similarity(vectors)[0][1]

            if similarity >= settings.MAX_LEVEL_OF_DUPLICATE_SIMILARITY:
                is_not_duplicate = False
                break
    return is_not_duplicate


def save_chat_id(telegram_link: TelegramLink, chat_id: int, session_name: Optional[str] = None):
    if telegram_link.chat_id:
        return
    with Session(engine) as session:
        telegram_link.chat_id = chat_id
        telegram_link.parser_account = session_name
        session.merge(telegram_link)
        session.commit()


def set_telegram_link_invalid(telegram_link: TelegramLink):
    with Session(engine) as session:
        telegram_link.invalid = True
        session.merge(telegram_link)
        session.commit()


def update_telegram_link_last_check(telegram_link: TelegramLink, last_check_at: datetime, last_message_id: int):
    with Session(engine) as session:
        telegram_link.last_check_at = last_check_at
        telegram_link.last_message_id = last_message_id
        session.merge(telegram_link)
        session.commit()

def set_telegram_link_chat_id_null(telegram_link: TelegramLink, session_name: Optional[str]):

    with Session(engine) as session:
        # session.refresh(telegram_link)
        if telegram_link.parser_account != session_name:
            return

        if telegram_link.parser_account == 'velinapp':
            return

        telegram_link.chat_id = None
        session.merge(telegram_link)
        session.commit()

def get_category_links_feature_enabled() -> bool:
    with Session(engine) as session:
        statement = select(GlobalSetting).where(GlobalSetting.key == "category_links_feature")
        result = session.exec(statement).first()
        return result.value == 'on' if result else False
