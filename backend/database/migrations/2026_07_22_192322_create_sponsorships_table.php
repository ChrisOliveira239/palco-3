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
        Schema::create('sponsorships', function (Blueprint $table) {
            $table->id();
            $table->morphs('sponsor');
            $table->morphs('alvo');
            $table->enum('spo_tipo_apoio', [
                'dinheiro', 'equipamento', 'figurino', 'alimentacao',
                'transporte', 'hospedagem', 'fotografia', 'filmagem', 'iluminacao', 'som', 'outro',
            ]);
            $table->decimal('spo_valor', 10, 2)->nullable();
            $table->text('spo_descricao')->nullable();
            $table->enum('spo_status', ['proposto', 'aceito', 'recusado', 'concluido'])->default('proposto');
            $table->boolean('spo_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsorships');
    }
};
