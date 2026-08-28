<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('guest_name')->nullable()->after('client_id');
            $table->string('guest_email')->nullable()->after('guest_name');
            $table->string('confirmation_token')->nullable()->unique()->after('status');
            $table->timestamp('confirmed_at')->nullable()->after('confirmation_token');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', fn (Blueprint $table) => $table->dropColumn(['guest_name', 'guest_email', 'confirmation_token', 'confirmed_at']));
    }
};