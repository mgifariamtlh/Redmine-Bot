<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 10)->default('info')->index(); // success | info | warning | error
            $table->string('source', 30)->index();                 // Auth, User Mapping, Jadwal Deploy, Redmine, Telegram, Settings, Scheduler
            $table->text('message');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // null = sistem / scheduler / bot
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
