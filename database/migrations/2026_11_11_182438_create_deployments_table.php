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
        Schema::create('deployments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('issue_id')->unique();
            $table->string('subject');
            $table->unsignedBigInteger('redmine_user_id')->nullable()->index();
            $table->string('pic_name')->nullable();
            $table->string('deploy_time')->nullable();
            $table->enum('status', ['test-passed', 'deployed', 'closed'])->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deployments');
    }
};
