<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country_economic_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('gdp', 20, 2)->nullable();
            $table->decimal('inflation', 8, 4)->nullable();
            $table->unsignedBigInteger('population')->nullable();
            $table->decimal('exports', 20, 2)->nullable();
            $table->decimal('imports', 20, 2)->nullable();
            $table->string('source')->default('world_bank');
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->unique(['country_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_economic_data');
    }
};
