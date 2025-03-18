from pathlib import Path

from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    api_id: int
    api_hash: str
    session_name: str

    api_id_2: int
    api_hash_2: str
    session_name_2: str

    api_id_3: int
    api_hash_3: str
    session_name_3: str

    api_id_4: int
    api_hash_4: str
    session_name_4: str

    api_id_5: int
    api_hash_5: str
    session_name_5: str

    api_id_6: int
    api_hash_6: str
    session_name_6: str

    api_id_7: int
    api_hash_7: str
    session_name_7: str

    db_url: str
    db_url_prod: str
    parser_service_url: str
    MAX_LEVEL_OF_DUPLICATE_SIMILARITY: float = 0.7
    project_root: Path = Path(__file__).parent.parent

    model_config = SettingsConfigDict(
        env_file='.env',
        env_file_encoding='utf-8',
        extra='ignore'
    )


settings = Settings()
