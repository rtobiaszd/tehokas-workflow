<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
            $table->json('config')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'workflow_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_conditions');
    }
};
