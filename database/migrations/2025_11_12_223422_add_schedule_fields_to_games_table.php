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
        Schema::table('games', function (Blueprint $table) {
            $table->timestamp('game_date')->nullable()->after('round');
            $table->string('home_team_code', 10)->nullable()->after('game_date');
            $table->string('away_team_code', 10)->nullable()->after('home_team_code');
            $table->integer('home_score')->nullable()->after('away_team_code');
            $table->integer('away_score')->nullable()->after('home_score');
            $table->boolean('is_played')->default(false)->after('away_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'game_date',
                'home_team_code',
                'away_team_code',
                'home_score',
                'away_score',
                'is_played',
            ]);
        });
    }
};
