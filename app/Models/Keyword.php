<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false; // Указывает, что id не является автоинкрементным
    protected $keyType = 'string'; // Указывает, что тип данных для id — это string

    protected $table = 'keywords';

    protected $fillable = [
        'id',
        'keyword',
        'category_id',
        'updated_at',
        'created_at',
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    }
}
