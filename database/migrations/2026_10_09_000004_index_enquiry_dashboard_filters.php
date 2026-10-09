<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->index(['status', 'appointment_date', 'id'], 'enquiries_status_date_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', fn (Blueprint $table) => $table->dropIndex('enquiries_status_date_id_index'));
    }
};
