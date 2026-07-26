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
        Schema::create('feed_posts', function (Blueprint $table) {
            $table->id();
            $table->morphs('autor');
            $table->enum('fee_tipo', Types::FEED_POST_TYPE);
            $table->text('fee_conteudo')->nullable();
            $table->string('fee_midia_url')->nullable();
            $table->boolean('fee_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_posts');
    }
};
