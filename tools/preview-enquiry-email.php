<?php

use App\Mail\EnquiryThankYou;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$html = (new EnquiryThankYou('Aarav'))->render();
$logo = 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('assets/ra-parikh-logo.png')));
$html = preg_replace('/src="cid:[^"]+"/', 'src="'.$logo.'"', $html);
$target = storage_path('app/private/enquiry-email-preview.html');
file_put_contents($target, $html);
echo "Saved email preview in storage/app/private/enquiry-email-preview.html. No email was sent.\n";
