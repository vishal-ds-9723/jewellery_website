<?php

return [
    'disk_name' => 'media',
    // Imports run via Artisan; no large HTTP upload is needed.
    'max_file_size' => 64 * 1024 * 1024,
    // Video encoding happens once, outside request handling.
    'queue_conversions_by_default' => false,
];
