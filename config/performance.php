<?php

return [
    // Bound synchronous downloads so a large export cannot occupy a PHP worker indefinitely.
    'export_max_rows' => (int) env('EXPORT_MAX_ROWS', 5000),
];
