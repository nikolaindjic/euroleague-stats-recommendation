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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->integer('game_code')->unique();
            $table->string('season_code');
            $table->boolean('is_live')->default(false);
            $table->string('referees')->nullable();
            $table->string('attendance')->nullable();
            $table->timestamps();

            $table->index(['season_code', 'game_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
