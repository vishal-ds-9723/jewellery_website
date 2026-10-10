<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$reportName = $argv[1] ?? 'browser-check.json';
if (! in_array($reportName, ['browser-check.json', 'concurrent-check.json'], true)) {
    throw new RuntimeException('Unknown verification report.');
}
$report = json_decode(file_get_contents(storage_path('app/private/'.$reportName)), true, flags: JSON_THROW_ON_ERROR);
$email = $report['email'];
if (! preg_match('/^qa-[a-f0-9-]{36}@example\.com$/', $email) || config('database.connections.mysql.database') !== 'ra_parikh' || config('database.default') !== 'mysql') {
    throw new RuntimeException('Unexpected smoke-check target.');
}
try {
    $enquiries = App\Models\Enquiry::where('email', $email)->count();
    $subscribers = App\Models\NewsletterSubscriber::where('email', $email)->count();
    $confirmations = Illuminate\Support\Facades\DB::table('jobs')->where('queue', 'enquiry-confirmations')->where('payload', 'like', '%'.$email.'%')->count();
    if (! $report['passed'] || $enquiries !== 1 || $subscribers !== 1 || $confirmations !== 1) {
        throw new RuntimeException("Browser result or MySQL counts failed: enquiries={$enquiries}, subscribers={$subscribers}, confirmations={$confirmations}.");
    }
    echo "MySQL verified: exactly one enquiry, one queued confirmation and one normalized newsletter signup persisted.\n";
} finally {
    Illuminate\Support\Facades\DB::table('jobs')->where('queue', 'enquiry-confirmations')->where('payload', 'like', '%'.$email.'%')->delete();
    App\Models\Enquiry::where('email', $email)->delete();
    App\Models\NewsletterSubscriber::where('email', $email)->delete();
    echo "Removed only this browser check's synthetic records.\n";
}
