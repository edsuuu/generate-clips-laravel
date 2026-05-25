<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->text('url');
            $table->string('source_provider')->nullable();
            $table->string('external_video_id')->nullable();
            $table->string('title')->nullable();
            $table->float('duration_seconds')->nullable();
            $table->foreignId('status_id')->constrained('statuses');
            $table->string('current_stage')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->timestamp('finished_at')->nullable();

            $table->index('status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
