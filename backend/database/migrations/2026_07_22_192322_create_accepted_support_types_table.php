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
        Schema::create('accepted_support_types', function (Blueprint $table) {
            $table->id();
            $table->morphs('alvo');
            $table->enum('ast_tipo_apoio', [
                'dinheiro', 'equipamento', 'figurino', 'alimentacao',
                'transporte', 'hospedagem', 'fotografia', 'filmagem', 'iluminacao', 'som', 'outro',
            ]);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['alvo_type', 'alvo_id', 'ast_tipo_apoio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accepted_support_types');
    }
};
