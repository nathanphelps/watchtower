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
        Schema::create('watchtower_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->unique();
            $table->string('queue')->index();
            $table->string('connection');
            $table->longText('payload');
            $table->string('status')->index(); // pending, processing, completed, failed
            $table->unsignedBigInteger('worker_id')->nullable()->index();
            $table->integer('attempts')->default(0);
            $table->longText('exception')->nullable();
            $table->timestamp('queued_at')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['status', 'queued_at']);
            $table->index(['queue', 'status']);
            $table->index(['connection', 'queue', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchtower_jobs');
    }
};
