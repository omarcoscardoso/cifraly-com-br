<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->string('original_key')->default('C')->after('artist');
            $table->unsignedSmallInteger('bpm')->nullable()->after('original_key');
            $table->string('time_signature')->default('4/4')->after('bpm');
            $table->string('spotify_url')->nullable()->after('time_signature');
            $table->string('youtube_url')->nullable()->after('spotify_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropColumn(['original_key', 'bpm', 'time_signature', 'spotify_url', 'youtube_url']);
        });
    }
};
