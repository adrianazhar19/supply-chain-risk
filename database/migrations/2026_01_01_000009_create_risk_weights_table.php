<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin-configurable weighting for the risk scoring engine (must sum to 100)
        Schema::create('risk_weights', function (Blueprint $table) {
            $table->id();
            $table->decimal('weather_weight', 5, 2)->default(30);
            $table->decimal('inflation_weight', 5, 2)->default(20);
            $table->decimal('political_weight', 5, 2)->default(40);
            $table->decimal('currency_weight', 5, 2)->default(10);
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_weights');
    }
};
