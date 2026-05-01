<?php

namespace App\Services;

use App\Models\SystemSetting;

class SystemSettingsService
{
    public function get(string $key, $default = null)
    {
        $row = SystemSetting::where('setting_key', $key)->first();
        if (! $row) {
            return $default;
        }

        $value = $row->setting_value;
        // Try JSON decode when appropriate
        $decoded = json_decode($value, true);

        return $decoded === null ? $value : $decoded;
    }

    public function set(string $key, $value, string $type = 'string', ?string $description = null): void
    {
        $payload = is_array($value) ? json_encode($value) : (string) $value;

        SystemSetting::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $payload, 'type' => $type, 'description' => $description]
        );
    }

    public function all(): array
    {
        return SystemSetting::get()->pluck('setting_value', 'setting_key')->map(function ($v) {
            $d = json_decode($v, true);

            return $d === null ? $v : $d;
        })->all();
    }
}
