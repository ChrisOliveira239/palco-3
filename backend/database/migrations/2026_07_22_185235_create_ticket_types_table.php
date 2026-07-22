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
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_session_id')->constrained('event_sessions')->cascadeOnDelete();
            $table->string('tit_nome');
            $table->decimal('tit_preco', 10, 2);
            $table->integer('tit_quantidade_total');
            $table->integer('tit_quantidade_vendida')->default(0);
            $table->dateTime('tit_venda_inicio')->nullable();
            $table->dateTime('tit_venda_fim')->nullable();
            $table->boolean('tit_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
