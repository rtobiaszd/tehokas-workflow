<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_settings', function (Blueprint $table) {

            // remover FK antiga
            $table->dropForeign(['tenant_id']);

            // remover unique antigo
            $table->dropUnique('tenant_settings_tenant_id_unique');

            // remover coluna legacy
            $table->dropColumn('settings');

            // nova estrutura
            $table->string('key')->after('tenant_id');
            $table->json('value')->after('key')->nullable();

            // recriar FK
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            // índice correto
            $table->unique(['tenant_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'key']);
            $table->dropForeign(['tenant_id']);

            $table->dropColumn(['key', 'value']);

            $table->longText('settings');

            $table->foreignId('tenant_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
        });
    }
};
