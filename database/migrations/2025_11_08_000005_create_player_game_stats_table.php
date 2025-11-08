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
        Schema::create('player_game_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('dorsal')->nullable();
            $table->boolean('is_starter')->default(false);
            $table->boolean('is_playing')->default(false);
            $table->string('minutes')->nullable();
            $table->integer('points')->default(0);
            $table->integer('field_goals_made_2')->default(0);
            $table->integer('field_goals_attempted_2')->default(0);
            $table->integer('field_goals_made_3')->default(0);
            $table->integer('field_goals_attempted_3')->default(0);
            $table->integer('free_throws_made')->default(0);
            $table->integer('free_throws_attempted')->default(0);
            $table->integer('offensive_rebounds')->default(0);
            $table->integer('defensive_rebounds')->default(0);
            $table->integer('total_rebounds')->default(0);
            $table->integer('assists')->default(0);
            $table->integer('steals')->default(0);
            $table->integer('turnovers')->default(0);
            $table->integer('blocks_favor')->default(0);
            $table->integer('blocks_against')->default(0);
            $table->integer('fouls_committed')->default(0);
            $table->integer('fouls_received')->default(0);
            $table->integer('valuation')->default(0);
            $table->integer('plus_minus')->default(0);
            $table->timestamps();

            $table->unique(['game_id', 'player_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_game_stats');
    }
};


