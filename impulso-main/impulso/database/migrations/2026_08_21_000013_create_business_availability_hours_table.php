<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_availability_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('day_of_week'); // 'monday', 'tuesday', etc.
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
            
            $table->unique(['business_id', 'day_of_week']);
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('appointments_enabled')->default(false)->after('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_availability_hours');
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('appointments_enabled');
        });
    }
};
