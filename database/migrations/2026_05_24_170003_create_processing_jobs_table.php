<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processing_jobs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // ingest, subtitle_full, recommend_cuts, render_cuts
            $table->string('provider')->default('python');
            $table->string('external_job_id')->nullable();
            $table->foreignId('status_id')->constrained('statuses');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('stage')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->timestamp('finished_at')->nullable();

            $table->index(['video_id', 'type']);
            $table->index('external_job_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processing_jobs');
    }
};
