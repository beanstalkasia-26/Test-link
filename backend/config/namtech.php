<?php

return [
    'enforce_two_factor' => (bool) env('ADMIN_ENFORCE_2FA', true),

    'two_factor_roles' => ['super-admin', 'publisher'],

    'enquiry_recipients' => array_filter(array_map('trim', explode(',', (string) env('NAMTECH_ENQUIRY_RECIPIENTS', '')))),

    'page_cache_ttl' => (int) env('PAGE_CACHE_TTL', 3600),

    'page_cache_enabled' => (bool) env('PAGE_CACHE_ENABLED', true),

    'embed_hosts' => array_filter(array_map('trim', explode(',', (string) env('EMBED_HOSTS', 'www.youtube-nocookie.com,www.youtube.com,player.vimeo.com,my.matterport.com,kuula.co')))),

    'max_upload_kb' => (int) env('MEDIA_MAX_UPLOAD_KB', 10240),

    'trusted_proxies' => env('TRUSTED_PROXIES') === '*' ? '*' : array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', '')))),
];
