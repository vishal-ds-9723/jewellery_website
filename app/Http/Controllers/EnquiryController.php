<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnquiryRequest;
use App\Models\Enquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class EnquiryController extends Controller
{
    public function store(StoreEnquiryRequest $request): JsonResponse|RedirectResponse
    {
        $enquiry = Enquiry::firstOrCreate(
            ['submission_id' => $request->validated('submission_id')],
            $request->safe()->except(['submission_id', 'website']),
        );

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
