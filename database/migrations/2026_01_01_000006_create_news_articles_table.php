<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // "news_cache": articles pulled from GNews, run through the lexicon sentiment engine
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url')->unique();
            $table->string('source_name')->nullable();
            $table->text('image_url')->nullable();
            $table->enum('category', ['logistics', 'trade', 'shipping', 'economy', 'geopolitics', 'other'])
                  ->default('other');
            $table->timestamp('published_at')->nullable();
            $table->unsignedSmallInteger('positive_score')->default(0);
            $table->unsignedSmallInteger('negative_score')->default(0);
            $table->enum('sentiment', ['Positive', 'Neutral', 'Negative'])->nullable();
            $table->timestamp('fetched_at')->useCurrent();
            $table->timestamps();

            $table->index(['country_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_articles');
    }
};
