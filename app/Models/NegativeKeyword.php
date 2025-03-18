<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NegativeKeyword extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'negative_keywords';

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
