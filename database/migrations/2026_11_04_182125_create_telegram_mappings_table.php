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
        Schema::create('telegram_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('redmine_user_id')->unique();
            $table->string('telegram_username')->nullable();
            $table->string('chat_id')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_mappings');
    }
};
