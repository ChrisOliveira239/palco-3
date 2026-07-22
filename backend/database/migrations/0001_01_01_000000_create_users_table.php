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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('use_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('use_avatar_url')->nullable();
            $table->text('use_bio')->nullable();
            $table->string('use_city')->nullable();
            $table->string('use_state')->nullable();
            $table->decimal('use_latitude', 10, 7)->nullable();
            $table->decimal('use_longitude', 10, 7)->nullable();
            $table->boolean('use_is_admin')->default(false);
            $table->enum('use_tipo_conta', ['pessoa', 'empresa'])->default('pessoa');
            $table->boolean('use_active')->default(true);
            $table->timestamps();

            $table->index(['use_city', 'use_state']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
