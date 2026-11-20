<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    public const Statuses = ['new' => 'New', 'shortlisted' => 'Shortlisted', 'rejected' => 'Not progressed', 'hired' => 'Hired'];

    protected $fillable = [
        'job_opening_id', 'first_name', 'last_name', 'email', 'phone', 'qualification', 'qualification_year', 'institution',
        'experience_years', 'current_company', 'location', 'resume_path', 'resume_name', 'consent', 'status', 'ip_address',
    ];

    protected function casts(): array
    {
        return ['consent' => 'boolean', 'notified_at' => 'datetime'];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class, 'job_opening_id');
    }
}
