<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'messages';

    protected $fillable = [
        'id',
        'text',
        'updated_at',
        'created_at',
        'from_username',
        'from_user_id',
        'date',

    ];

    public function categories() {
        return $this->belongsToMany(Category::class);
    }

    public function telegramLink() {
    return $this->belongsTo(TelegramLink::class, 'chat_id', 'chat_id');
   }
}
