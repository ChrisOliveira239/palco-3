<?php

use App\Enums\Types;
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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('eve_titulo');
            $table->text('eve_descricao');
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->morphs('organizador');
            $table->enum('eve_status', Types::EVENT_STATUS)->default('RASCUNHO');
            $table->boolean('eve_gratuito')->default(false);
            $table->string('eve_cartaz_url')->nullable();
            $table->json('eve_links_externos')->nullable();
            $table->foreignId('aprovado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('eve_aprovado_em')->nullable();
            $table->boolean('eve_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
