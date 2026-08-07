<?php

declare(strict_types=1);

return [

    'sitemap' => [
        'enabled' => true,
        'exclude_prefixes' => ['/admin', '/api'],
        'default_changefreq' => 'weekly',
        'default_priority' => 0.5,
    ],

    'robots' => [
        'enabled' => true,
        'disallow' => ['/admin', '/api'],
        'sitemap_url' => '/sitemap.xml'
    ]

];