<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transcripts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->string('language', 16)->nullable();
            $table->float('duration_seconds')->nullable();
            $table->longText('raw_text')->nullable();
            $table->longText('validated_text')->nullable();
            $table->longText('edited_text')->nullable();
            $table->string('active_text_source')->default('raw'); // raw, validated, edited
            $table->boolean('is_validated_by_ai')->default(false);
            $table->boolean('is_confirmed_by_user')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->timestamps();

            $table->unique('video_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transcripts');
    }
};
