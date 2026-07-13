<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('rainfall', 6, 2)->nullable();
            $table->decimal('wind_speed', 6, 2)->nullable();
            $table->unsignedTinyInteger('storm_risk')->default(0);
            $table->timestamp('fetched_at')->useCurrent();
            $table->timestamps();

            $table->index(['country_id', 'fetched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_snapshots');
    }
};
