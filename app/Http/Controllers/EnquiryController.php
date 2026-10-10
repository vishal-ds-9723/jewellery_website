<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnquiryRequest;
use App\Mail\EnquiryThankYou;
use App\Models\Enquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EnquiryController extends Controller
{
    public function store(StoreEnquiryRequest $request): JsonResponse|RedirectResponse
    {
        $enquiry = DB::transaction(function () use ($request): Enquiry {
            // Try the unique insert first: concurrent retries can then read the
            // winning row without an earlier MySQL repeatable-read snapshot.
            $enquiry = Enquiry::query()->createOrFirst(
                ['submission_id' => $request->validated('submission_id')],
                $request->safe()->except(['submission_id', 'website']),
            );

            if ($enquiry->wasRecentlyCreated) {
                Mail::mailer('smtp')->to($enquiry->email)->queue(new EnquiryThankYou($enquiry->name));
            }

            return $enquiry;
        }, 3);

        $message = 'Thank you. Your enquiry has been saved. Our team will contact you to confirm your preferred slot.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'submission_id' => (string) Str::uuid(),
            ], $enquiry->wasRecentlyCreated ? 201 : 200);
        }

        return to_route('contact')->with('success', $message);
    }
}
