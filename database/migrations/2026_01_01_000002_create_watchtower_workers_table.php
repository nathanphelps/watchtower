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
        Schema::create('watchtower_workers', function (Blueprint $table) {
            $table->id();
            $table->string('worker_id')->unique();
            $table->string('supervisor');
            $table->string('queue');
            $table->integer('pid')->nullable();
            $table->string('status'); // running, paused, stopped
            $table->timestamp('started_at');
            $table->timestamp('last_heartbeat')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index(['supervisor', 'status']);
            $table->index(['status', 'last_heartbeat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchtower_workers');
    }
};
