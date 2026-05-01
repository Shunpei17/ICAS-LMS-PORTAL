<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_slug',
        'topic_index',
        'title',
        'body',
        'type',
        'file_path',
        'original_filename',
        'icon',
    ];

    protected $casts = [
        'topic_index' => 'integer',
    ];
}
