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
        Schema::table('events', function (Blueprint $table) {
            $table->index(['organization_id', 'starts_at'], 'events_org_starts_at_idx');
            $table->index(['organization_id', 'status'], 'events_org_status_idx');
        });

        Schema::table('songs', function (Blueprint $table) {
            $table->index(['organization_id', 'title'], 'songs_org_title_idx');
        });

        Schema::table('event_rosters', function (Blueprint $table) {
            $table->index(['event_id', 'user_id'], 'event_rosters_event_user_idx');
        });

        Schema::table('event_songs', function (Blueprint $table) {
            $table->index(['event_id', 'order_index'], 'event_songs_event_order_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_songs', function (Blueprint $table) {
            $table->dropIndex('event_songs_event_order_idx');
        });

        Schema::table('event_rosters', function (Blueprint $table) {
            $table->dropIndex('event_rosters_event_user_idx');
        });

        Schema::table('songs', function (Blueprint $table) {
            $table->dropIndex('songs_org_title_idx');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_org_status_idx');
            $table->dropIndex('events_org_starts_at_idx');
        });
    }
};
