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
        'detail',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Standardised helper to log system actions.
     */
    public static function log(string $action, string $module, ?string $detail = null): void
    {
        self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'detail' => $detail,
        ]);
    }
}
