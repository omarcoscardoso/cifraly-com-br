<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('invite_code', 32)->nullable()->unique()->after('slug');
        });

        Schema::table('organization_user', function (Blueprint $table) {
            $table->string('role', 20)->default('member')->after('user_id');
        });

        // Backfill existing organizations with invite codes
        $organizations = DB::table('organizations')->get();
        foreach ($organizations as $org) {
            DB::table('organizations')->where('id', $org->id)->update([
                'invite_code' => strtoupper(Str::random(8)),
            ]);
        }

        // Backfill existing organization users as admin
        DB::table('organization_user')->update(['role' => 'admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_user', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('invite_code');
        });
    }
};
