<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    protected $fillable = ['email', 'status', 'consented_at', 'consent_text', 'source_url', 'ip_address', 'unsubscribe_token', 'unsubscribed_at'];

    protected function casts(): array
    {
        return ['consented_at' => 'datetime', 'unsubscribed_at' => 'datetime'];
    }
}
