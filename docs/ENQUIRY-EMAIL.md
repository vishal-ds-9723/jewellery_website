# Enquiry confirmation emails

Each new, valid appointment enquiry queues a confirmation for the email address entered on the form. It includes the R.A. Parikh logo above the brand heading as an inline PNG without a downloadable filename, the customer's name, a thank-you message, and a note that the team will get in touch. An enquiry acknowledgement does not confirm an appointment.

The email has both HTML and plain-text versions. The HTML uses inline styles and an embedded logo, so it does not depend on a localhost image URL. Customer names are escaped in HTML. No admin CC/BCC is added.

## Sender setup

The local app password is stored only in the ignored .env file. The local sender is configured and Gmail SMTP authentication has been verified. Authentication testing does not itself send a test email or verify inbox placement. Do not commit credentials.

Configure the following using the Gmail account that owns the app password:

    MAIL_MAILER=smtp
    MAIL_SCHEME=smtp
    MAIL_HOST=smtp.gmail.com
    MAIL_PORT=587
    MAIL_USERNAME=your-gmail-address
    MAIL_PASSWORD=your-google-app-password
    MAIL_FROM_ADDRESS=your-gmail-address
    MAIL_FROM_NAME="R.A. Parikh Jewellers"
    MAIL_TIMEOUT=20

Then refresh the configuration:

    php artisan config:cache

Start the dedicated worker in a separate terminal and leave it running:

    php artisan app:work-enquiry-mail

The command checks that a sender, username and password are configured before processing pending emails. Actual SMTP authentication is checked when the worker sends. It never falls back to the log mailer. If the worker is stopped, saved confirmations remain queued in MySQL.

On this Windows machine, you can also start it in the background:

    .\tools\start-mail-worker.ps1

The launcher avoids starting a duplicate worker and writes logs to storage/logs/enquiry-mail-worker*.log. Start it again after restarting Windows. To stop gracefully, run php artisan queue:restart. After changing SMTP settings, refresh config, stop the worker, wait for it to exit, then start it again.

On hosting, run this worker under your process manager and restart it after deploying code or changing SMTP configuration. XAMPP Apache alone does not process Laravel queue jobs.

## Reliability

EnquiryThankYou explicitly uses the database queue and the enquiry-confirmations queue name, independently of the default queue connection. Keep the database queue on the SAME database connection as the enquiries table (the current configuration does this; do not point DB_QUEUE_CONNECTION elsewhere).

The enquiry and queue job are inserted in the same database transaction. A worker cannot see the job until commit. If storing the job fails, the enquiry insert also rolls back, so the user can retry the same submission ID. The unique submission ID prevents an HTTP retry from creating another enquiry or confirmation job.

SMTP delivery happens outside the HTTP request, with a 20-second socket timeout, up to three attempts, and 60/300-second retry delays. Failed deliveries are retained in failed_jobs:

    php artisan queue:failed
    php artisan queue:retry JOB_UUID

SMTP acceptance does not guarantee inbox placement. A connection failure after provider acceptance can also cause a retry to deliver a duplicate; SMTP has no exactly-once guarantee. Submission deduplication prevents repeated form requests from creating extra jobs.

Newsletter signup does not send this appointment-enquiry acknowledgement.

## Preview and tests

Generate a local preview without sending an email:

    php tools/preview-enquiry-email.php

Open storage/app/private/enquiry-email-preview.html.

The feature tests check recipient selection, one queued email across retries, validation failures, atomic rollback on queue failure, embedded logo, HTML escaping, plain text, and refusal to consume pending jobs while sender configuration is incomplete. The SMTP transport is replaced by an in-memory transport for delivery tests, so no external test messages are sent.

[Laravel mail documentation](https://laravel.com/framework/docs/13.x/mail)

## Logo display and spam diagnosis

The email contains one logo, above the R.A. PARIKH heading, and no footer image. Its CID matches a single inline PNG MIME part without a downloadable filename, to avoid advertising it as a separate file. Email clients ultimately control attachment previews and may still show inline images in their attachment interface. The image is embedded; it needs no publicly accessible localhost URL.

Gmail can block images when it considers a message suspicious. Removing an attachment filename cannot override that protection or guarantee inbox placement. Check the received email's Show original page for SPF, DKIM and DMARC results and the spam-warning reason. For this gmail.com sender, Google manages the sending domain's authentication; do not add gmail.com DNS records to the website's domain. A future custom-domain sender needs authentication configured with its mail provider.

[Google image display help](https://support.google.com/mail/answer/145919?hl=en)
[Google sender guidelines](https://support.google.com/mail/answer/81126?hl=en)
