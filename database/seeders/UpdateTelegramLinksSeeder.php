<?php

namespace Database\Seeders;

use App\Models\TelegramLink;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UpdateTelegramLinksSeeder extends Seeder
{
    public function run()
    {
        TelegramLink::query()->chunkById(100, function ($links) {
            foreach ($links as $link) {
                // Очистка ссылки
                $updatedLink = preg_replace('/^https?:\/\//', '', $link->link); // Удаляем протокол
                $updatedLink = rtrim($updatedLink, '/'); // Удаляем слэш в конце
                $updatedLink = ltrim($updatedLink, '@'); // Удаляем @ в начале
                $updatedLink = trim($updatedLink); // Удаляем пробелы и невидимые символы

                // Отладочный вывод
                $this->command->info('Processing link: ' . $link->link);
                $this->command->info('Cleaned link: ' . $updatedLink);

                // Если ссылка начинается с "t.me/" и за ним идёт отрицательное число
                if (Str::startsWith($updatedLink, 't.me/')) {
                    $numberPart = Str::after($updatedLink, 't.me/'); // Получаем часть после "t.me/"
                    if (preg_match('/^-?\d+$/', $numberPart)) {
                        // Если это отрицательное число, убираем "t.me/"
                        $updatedLink = $numberPart;
                        $this->command->info('Removed t.me/ from numeric link: ' . $updatedLink);
                    }
                }

                // Проверяем, является ли ссылка числовым значением (например, -4776385780)
                if (preg_match('/^-?\d+$/', $updatedLink)) {
                    // Если ссылка числовая, оставляем её без изменений
                    $this->command->info('Numeric link detected: ' . $updatedLink);
                } else {
                    // Если ссылка не числовая, добавляем "t.me/"
                    if (Str::startsWith($updatedLink, 't.me/') === false) {
                        $updatedLink = "t.me/" . $updatedLink;
                    }
                }

                // Отладочный вывод
                $this->command->info('Updated link: ' . $updatedLink);

                if ($link->link !== $updatedLink) {
                    $link->invalid = null;
                    $this->command->info('fix ' . $link->link . ' to ' . $updatedLink);
                }

                // Поиск дубликатов
                $existingLink = TelegramLink::where('link', $updatedLink)->where('id', '!=', $link->id)->first();

                if (!$existingLink) {
                    // Если дубликат не найден, обновляем ссылку
                    $link->link = $updatedLink;
                    $link->save();
                    $this->command->info('Link updated: ' . $updatedLink);
                } else {
                    // Если дубликат найден, переносим категории и удаляем текущий линк
                    $categories = $link->categories->pluck('id')->toArray();
                    $existingLink->categories()->syncWithoutDetaching($categories);
                    $this->command->info('Duplicate found: ' . $updatedLink);
                    $this->command->info('Categories transferred: ' . implode(', ', $categories));
                    $link->delete();
                    $this->command->info('Link deleted: ' . $link->link);
                }
            }
        });
        $this->command->info('Telegram links have been updated.');
    }
}