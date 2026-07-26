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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->morphs('criador');
            $table->string('opp_titulo');
            $table->text('opp_descricao');
            $table->foreignId('skill_id')->nullable()->constrained('skills')->nullOnDelete();
            $table->string('opp_cidade')->nullable();
            $table->enum('opp_status', Types::OPPORTUNITY_STATUS)->default('ABERTA');
            $table->boolean('opp_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
