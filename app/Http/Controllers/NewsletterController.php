<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsletterRequest;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class NewsletterController extends Controller
{
    public function store(StoreNewsletterRequest $request): JsonResponse|RedirectResponse
    {
        NewsletterSubscriber::firstOrCreate(['email' => $request->validated('email')]);
        $message = 'Thank you. Your newsletter signup has been saved.';

        return $request->expectsJson()
            ? response()->json(['message' => $message])
            : redirect(route('home').'#contact')->with('success', $message);
    }
}
