<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Enquiry extends Model
{
    public const STATUSES = ['new', 'contacted', 'confirmed', 'closed'];

    protected $fillable = [
        'submission_id', 'name', 'email', 'phone', 'shopping_interest',
        'appointment_date', 'appointment_slot', 'message',
    ];

    protected function appointmentDate(): Attribute
    {
        // Persist a DATE, without an implicit midnight time component.
        return Attribute::make(set: fn ($value) => Carbon::parse($value)->toDateString());
    }

    protected function casts(): array
    {
        return ['appointment_date' => 'date'];
    }
}
