<?php

namespace App\Http\Controllers;

use App\Models\TelegramLink;
use Illuminate\Http\Request;

class TelegramLinkController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'telegram_link' => 'required|string|max:255',
            'telegram_link_raw' => 'required|string|max:255',
            'is_private' => 'required|boolean' // Добавляем валидацию
        ]);

        $telegramLink = TelegramLink::updateOrCreate(
            ['link' => $validated['telegram_link']],
            [
                'link_raw' => $validated['telegram_link_raw'],
                'is_private' => $validated['is_private'] // Сохраняем в БД
            ]
        );

        return response()->json($telegramLink, 201);
    }
}