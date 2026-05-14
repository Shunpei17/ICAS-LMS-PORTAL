<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'classroom_id',
        'name',
        'order',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class)->orderBy('created_at');
    }
}
