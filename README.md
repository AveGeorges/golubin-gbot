[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2Fcf36aa7f-ad86-49d6-a9d2-9d04fb02a49d%3Fdate%3D1%26commit%3D1&style=plastic)](https://forge.laravel.com/servers/711366/sites/2307808)

# GBot

Changelog for Admin Panel

1.0.0 - Initial release

1.0.1 - Added field to telegram links (is_private) 
        to: TelegramLinkResource.php, TelegramLink.php

1.0.2 - Create: database\migrations\2025_03_17_183926_add_is_private_to_telegram_links_table.php

1.0.3 - Added filters to messages (is_private, is_public)
        to: MessageResource.php

1.0.4 - Added filters to telegram links (is_private, is_public) 
        to: TelegramLinkResource.php

1.0.5 - Change $table->uuid('id')->primary(); 
        to: database\migrations\2024_05_29_154034_create_keywords_table.php

1.0.6 - Change $table->uuid('id')->primary(); 
        to: database\migrations\2024_06_05_190323_create_messages_table.php

1.0.7 - Change $table->uuid('id')->primary();
        to: database\migrations\2024_06_07_143021_create_negative_keywords_table.php

1.0.8 - Add: public $incrementing = false; // Указывает, что id не является автоинкрементным
        to: Keyword.php

1.0.9 - Add:     
        public function telegramLink() {
            return $this->belongsTo(TelegramLink::class, 'chat_id', 'chat_id');
        }
        to: Message.php

1.0.10 - Create: app\Http\Controllers\TelegramLinkController.php

1.0.11 - Change: 
         foreach ($categories as $categoryData) {
            Category::firstOrCreate(
               ['id' => $categoryData['id']],
               ['name' => $categoryData['name']]
            );
         }
         to: database\seeders\CategorySeeder.php

1.0.12 - Change: 
        foreach ($keywords as $keywordData) {
            Keyword::create($keywordData);
        }
        to: database\seeders\KeywordsSeeder.php

1.0.13 - Change: 
        // Проверка существования пользователя
        $userAdmin = User::firstOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => 'Admin', // Добавьте значение для 'name'
                'password' => Hash::make(self::ADMIN_PASSWORD)
            ]
        );
        $userAdmin->assignRole(Role::ROLE_ADMIN);
        to: database\seeders\UserSeeder.php

1.0.14 - Add:
        public function setLinkAttribute(string $value)
        to: TelegramLink.php
        


Changelog for Bot

1.0.0 - Initial release

1.0.1 - Add:
         filter for private links in get_tg_links function
         to: py/Golubin-parsing-chats/src/database/operations.py

1.0.2 - Add:
         filter for private links in get_tg_links function
         to: py/Golubin-parsing-chats/src/database/operations.py

1.0.3 - Add:
         condition     
         if telegram_link.is_private:
            return link  # Для закрытых групп возвращаем ссылку как есть
         to: py/Golubin-parsing-chats/src/helpers.py

1.0.4 - Add:
         parameter is_private to run function
         to: py/Golubin-parsing-chats/main.py



