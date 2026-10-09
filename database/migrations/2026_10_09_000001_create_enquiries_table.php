<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->uuid('submission_id')->unique();
            $table->string('name', 100);
            $table->string('email');
            $table->string('phone', 30);
            $table->string('shopping_interest', 20)->nullable();
            $table->date('appointment_date')->index();
            $table->string('appointment_slot', 20);
            $table->text('message');
            $table->string('status', 20)->default('new')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
