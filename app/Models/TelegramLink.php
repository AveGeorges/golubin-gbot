<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class TelegramLink extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'telegram_links';

    protected $fillable = [
        'link',
        'link_raw',
        'chat_id',       
        'last_check_at',   
        'last_message_id', 
        'created_at',
        'updated_at',
        'invalid',
        'parser_account',
        'is_private',
    ];

    public function categories() {
        return $this->belongsToMany(Category::class);
    }
    public function setLinkAttribute(string $value)
    {
        // Очистка ссылки
        $link = preg_replace('/^https?:\/\//', '', $value); // Удаляем протокол
        $link = rtrim($link, '/'); // Удаляем слэш в конце
        $link = ltrim($link, '@'); // Удаляем @ в начале
        $link = trim($link); // Удаляем пробелы и невидимые символы

        // Если ссылка начинается с "t.me/" и за ним идёт отрицательное число
        if (Str::startsWith($link, 't.me/')) {
            $numberPart = Str::after($link, 't.me/'); // Получаем часть после "t.me/"
            if (preg_match('/^-?\d+$/', $numberPart)) {
                // Если это отрицательное число, убираем "t.me/"
                $link = $numberPart;
            }
        }

        // Если ссылка не числовая, добавляем "t.me/"
        if (!preg_match('/^-?\d+$/', $link)) {
            if (Str::startsWith($link, 't.me/') === false) {
                $link = "t.me/" . $link;
            }
        }

        // Сохраняем обработанную ссылку
        $this->attributes['link'] = $link;
    }
}
