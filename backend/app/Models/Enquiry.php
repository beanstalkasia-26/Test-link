<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends Model
{
    public const Types = ['contact' => 'General contact', 'program' => 'Program enquiry', 'partnership' => 'Partnership enquiry', 'case_study' => 'Case study request', 'brochure' => 'Brochure download'];

    public const Statuses = ['new' => 'New', 'in_progress' => 'In progress', 'closed' => 'Closed'];

    protected $fillable = [
        'type', 'program_id', 'name', 'email', 'phone', 'organisation', 'subject', 'message',
        'consent', 'consent_text', 'source_url', 'ip_address', 'user_agent', 'status',
    ];

    protected function casts(): array
    {
        return ['consent' => 'boolean', 'notified_at' => 'datetime'];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
