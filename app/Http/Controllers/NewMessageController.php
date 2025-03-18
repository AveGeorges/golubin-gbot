<?php

namespace App\Http\Controllers;

use App\Models\TelegramLink;
use Illuminate\Http\Request;

class NewMessageController extends Controller
{
    /**
     *   @OA\Info(
     *       version="1.0.0",
     *       title="Golubin bot Swagger API",
     *       description="L5 Swagger API description",
     * )
     *
     * @OA\Post(
     *     tags={"NewMessage"},
     *     path="/api/new-message",
     *     description="Get new message and link to telegram channel",
     *
     *     @OA\Parameter(
     *           name="category",
     *           in="query",
     *           required=true,
     *           @OA\Schema(
     *               type="integer",
     *               example="3"
     *           )
     *      ),
     *
     *     @OA\Parameter(
     *            name="telegram_link",
     *            in="query",
     *            required=true,
     *            @OA\Schema(
     *                type="string",
     *                example="tg.me/blabla"
     *            )
     *       ),
     *
     *     @OA\Parameter(
     *            name="telegram_link_raw",
     *            in="query",
     *            required=true,
     *            @OA\Schema(
     *                type="string",
     *                example="tg.me/blabla/123"
     *            )
     *       ),
     *
     *     @OA\Response(
     *          response="default",
     *          description="Success"),
     *          @OA\MediaType(
     *                mediaType="application/json",
     *                @OA\Schema(
     *                  @OA\Property(property="data"),
     *                ),
     *          )
     *    )
     *
     *
     */
    public function store(Request $request)
    {
        $request->validate([
            'telegram_link' => 'required',
            'telegram_link_raw' => 'required',
            'category' => 'required|int',
        ]);

        $telegramLink = TelegramLink::firstOrCreate(
            ['link' => $request->telegram_link],
            ['link_raw' => $request->telegram_link_raw]
        );

        $telegramLink->categories()->syncWithoutDetaching($request->category);

        return response()->json($telegramLink, 200);
    }
}
