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
        Schema::create('artist_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('art_nome_artistico');
            $table->text('art_bio')->nullable();
            $table->string('art_capa_url')->nullable();
            $table->boolean('art_verificado')->default(false);
            $table->string('art_drt')->nullable();
            $table->string('art_telefone')->nullable();
            $table->string('art_email')->nullable();
            $table->string('art_site')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artist_profiles');
    }
};
