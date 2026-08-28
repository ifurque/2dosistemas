<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('profile_photo')->nullable()->after('logo');
            $table->string('cover_photo')->nullable()->after('profile_photo');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('photo'));
        Schema::table('businesses', fn (Blueprint $table) => $table->dropColumn(['profile_photo', 'cover_photo']));
    }
};