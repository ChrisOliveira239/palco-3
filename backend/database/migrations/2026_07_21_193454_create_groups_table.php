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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('gro_nome');
            $table->text('gro_descricao')->nullable();
            $table->string('gro_avatar_url')->nullable();
            $table->string('gro_capa_url')->nullable();
            $table->string('gro_cnpj')->nullable();
            $table->string('gro_telefone')->nullable();
            $table->string('gro_email')->nullable();
            $table->string('gro_site')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
