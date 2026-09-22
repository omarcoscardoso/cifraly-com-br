<?php

declare(strict_types=1);

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
        Schema::create('event_songs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_version_id')->nullable()->constrained('song_versions')->nullOnDelete();
            $table->string('target_key', 10);
            $table->unsignedInteger('order_index')->default(1);
            $table->string('arrangement_notes', 255)->nullable();
            $table->timestamps();

            $table->index(['event_id', 'order_index']);
            $table->index(['organization_id', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_songs');
    }
};
