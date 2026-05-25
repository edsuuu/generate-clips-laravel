<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('index');
            $table->string('name', 32);          // PT1, PT2
            $table->string('type', 32);          // pt1, pt2, manual, ai_recommended
            $table->string('source', 16);        // manual, ai
            $table->float('start_seconds');
            $table->float('end_seconds');
            $table->float('duration_seconds');
            $table->float('score')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('status_id')->constrained('statuses');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->json('hashtags')->nullable();
            $table->timestamps();
            $table->timestamp('rendered_at')->nullable();

            $table->index(['video_id', 'index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuts');
    }
};
