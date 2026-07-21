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
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('ven_nome');
            $table->string('ven_endereco');
            $table->string('ven_cidade');
            $table->string('ven_estado');
            $table->decimal('ven_latitude', 10, 7);
            $table->decimal('ven_longitude', 10, 7);
            $table->timestamps();

            $table->index(['ven_cidade', 'ven_estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
