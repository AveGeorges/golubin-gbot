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
                $updatedLink = preg_replace('/^https?:\/\//', '', $link->link);
                $updatedLink = rtrim($updatedLink, '/');
                $updatedLink = ltrim($updatedLink, '@');

                if (Str::startsWith($updatedLink, 't.me/') === false) {
                    $updatedLink = "t.me/" . $updatedLink;
                }

                if ($link->link !== $updatedLink) {
                    $link->invalid = null;
                    $this->command->info('fix ' . $link->link . ' to ' . $updatedLink);
                }


                $existingLink = TelegramLink::where('link', $updatedLink)->where('id', '!=', $link->id)->first();

                if (!$existingLink) {
                    $link->link = $updatedLink;
                    $link->save();
                } else {
                    // Перенос категорий
                    $categories = $link->categories->pluck('id')->toArray();
                    $existingLink->categories()->syncWithoutDetaching($categories);
                    $this->command->info('dublicate ' . $updatedLink);
                    // Удаление текущего линка
                    $link->delete();
                }
            }
        });
        $this->command->info('Telegram links have been updated.');
    }
}