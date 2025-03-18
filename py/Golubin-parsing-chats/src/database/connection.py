from sqlmodel import create_engine

from src.config import settings

engine = create_engine(settings.db_url)

engine_prod = create_engine(settings.db_url_prod)
