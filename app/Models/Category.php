<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'id',
        'name',
        'updated_at',
        'created_at',
        'global_search'
    ];

    public function telegram_links() {
        return $this->belongsToMany(TelegramLink::class);
    }
    public function keywords() {
        return $this->hasMany(Keyword::class);
    }

    public function negative_keywords() {
        return $this->hasMany(NegativeKeyword::class);
    }

    public function messages() {
        return $this->belongsToMany(Message::class);
    }
}
