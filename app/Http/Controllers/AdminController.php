<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    private function enquiries(Request $request): Builder
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(Enquiry::STATUSES)],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        return Enquiry::query()
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            // This column is already DATE: avoid wrapping it in a SQL function.
            ->when($filters['date'] ?? null, fn (Builder $query, string $date) => $query->where('appointment_date', $date));
    }

    public function index(Request $request): View
    {
        return view('admin.index', [
            'enquiries' => $this->enquiries($request)->orderByDesc('id')->cursorPaginate(20, ['*'], 'enquiries_cursor')->withQueryString(),
            'subscribers' => NewsletterSubscriber::orderByDesc('id')->cursorPaginate(20, ['id', 'email', 'created_at'], 'subscribers_cursor')->withQueryString(),
            'statuses' => Enquiry::STATUSES,
            'exportLimit' => $this->exportLimit(),
        ]);
    }

    public function update(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', Rule::in(Enquiry::STATUSES)]]);
        $enquiry->status = $validated['status'];
        $enquiry->save();

        return back()->with('success', 'Enquiry status updated.');
    }

    private function exportLimit(): int
    {
        return max(1, min(10000, (int) config('performance.export_max_rows', 5000)));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->enquiries($request)->orderBy('id');
        $limit = $this->exportLimit();
        // A bounded indexed read; do not COUNT the whole table or collect messages here.
        $ids = (clone $query)->limit($limit + 1)->pluck('id');
        if ($ids->count() > $limit) {
            throw ValidationException::withMessages([
                'export' => "This export contains more than {$limit} enquiries. Filter by appointment date or status and try again.",
            ]);
        }

        // Do not include new arrivals after the export starts.
        $query->where('id', '<=', $ids->last() ?? 0);

        return response()->streamDownload(function () use ($query, $limit): void {
            $stream = fopen('php://output', 'w');
            try {
                fputcsv($stream, ['ID', 'Name', 'Email', 'Phone', 'Interest', 'Appointment date (India)', 'Slot', 'Message', 'Status', 'Submitted (UTC)'], ',', '"', '');
                $written = 0;
                foreach ($query->lazyById(500) as $enquiry) {
                    if ($written++ >= $limit || connection_aborted()) {
                        break;
                    }
                    $row = [
                        $enquiry->id, $enquiry->name, $enquiry->email, $enquiry->phone,
                        $enquiry->shopping_interest, $enquiry->appointment_date->format('Y-m-d'),
                        $enquiry->appointment_slot, $enquiry->message, $enquiry->status,
                        $enquiry->created_at->toDateTimeString(),
                    ];
                    $row = array_map(fn ($value) => preg_match('/^[\s]*[=+@\-\t\r\n]/u', (string) $value) ? "'".$value : $value, $row);
                    fputcsv($stream, $row, ',', '"', '');
                }
            } finally {
                fclose($stream);
            }
        }, 'enquiries-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }
}
