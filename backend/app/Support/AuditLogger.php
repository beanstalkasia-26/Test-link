<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLogger
{
    public static function log(string $action, ?Model $subject, string $description, array $properties = [], ?User $user = null): void
    {
        $request = app()->runningInConsole() ? null : request();

        AuditLog::create([
            'user_id' => ($user ?? $request?->user())?->id,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => Str::limit($description, 250),
            'properties' => $properties ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => Str::limit((string) $request?->userAgent(), 490),
        ]);
    }
}
