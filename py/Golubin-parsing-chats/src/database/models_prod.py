from datetime import datetime

from sqlalchemy import SmallInteger
from sqlmodel import Field, SQLModel


class NewMessages(SQLModel, table=True):
    __tablename__ = "new_messages_demo"

    id: int = Field(primary_key=True)
    category: int = Field(SmallInteger)
    text: str
    date: datetime
    sended: bool = Field(default=False)



