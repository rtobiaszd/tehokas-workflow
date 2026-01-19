<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ⚠️ APENAS EM DEV / LOCAL
        // Remove dados antigos incompatíveis com JSON
        DB::table('tenant_settings')->delete();
    }

    public function down(): void
    {
        // irreversível (ok em dev)
    }
};
