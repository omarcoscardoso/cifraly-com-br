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
            $table->unsignedTinyInteger('capo_fret')->nullable()->after('original_key');
        });

        Schema::table('song_versions', function (Blueprint $table) {
            $table->unsignedTinyInteger('capo_fret')->nullable()->after('base_key');
        });

        Schema::table('event_songs', function (Blueprint $table) {
            $table->unsignedTinyInteger('capo_fret')->nullable()->after('target_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropColumn('capo_fret');
        });

        Schema::table('song_versions', function (Blueprint $table) {
            $table->dropColumn('capo_fret');
        });

        Schema::table('event_songs', function (Blueprint $table) {
            $table->dropColumn('capo_fret');
        });
    }
};
