<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'ip_address',
        'user_agent',
        'timestamp',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
