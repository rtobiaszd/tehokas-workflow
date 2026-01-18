<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_conditions', function (Blueprint $table) {
            $table->json('payload')->nullable()->after('config');
        });

        Schema::table('workflow_actions', function (Blueprint $table) {
            $table->json('payload')->nullable()->after('config');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_conditions', function (Blueprint $table) {
            $table->dropColumn('payload');
        });

        Schema::table('workflow_actions', function (Blueprint $table) {
            $table->dropColumn('payload');
        });
    }
};
