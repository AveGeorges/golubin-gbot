<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
