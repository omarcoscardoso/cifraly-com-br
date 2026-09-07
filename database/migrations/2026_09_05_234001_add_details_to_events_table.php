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
        Schema::table('events', function (Blueprint $table): void {
            $table->foreignId('team_id')->nullable()->after('organization_id')->constrained('teams')->nullOnDelete();
            $table->string('status', 30)->default('draft')->after('title');
            $table->dateTime('starts_at')->nullable()->after('status');
            $table->dateTime('rehearsal_at')->nullable()->after('starts_at');
            $table->text('notes')->nullable()->after('rehearsal_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropForeign(['team_id']);
            $table->dropColumn([
                'team_id',
                'status',
                'starts_at',
                'rehearsal_at',
                'notes',
            ]);
        });
    }
};
