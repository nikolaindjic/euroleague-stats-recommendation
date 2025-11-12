<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Enums\Position;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalize positions in players table
        $this->normalizeTable('players');

        // Normalize positions in player_game_stats table
        $this->normalizeTable('player_game_stats');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse normalization
    }

    /**
     * Normalize position data in a table
     */
    private function normalizeTable(string $table): void
    {
        // Update Guards
        DB::table($table)
            ->where('position', 'LIKE', '%Guard%')
            ->orWhere('position', 'LIKE', '%guard%')
            ->orWhere('position', 'LIKE', '%PG%')
            ->orWhere('position', 'LIKE', '%SG%')
            ->orWhere('position', 'LIKE', '%Point%')
            ->orWhere('position', 'LIKE', '%Shooting%')
            ->update(['position' => Position::GUARD]);

        // Update Forwards
        DB::table($table)
            ->where('position', 'LIKE', '%Forward%')
            ->orWhere('position', 'LIKE', '%forward%')
            ->orWhere('position', 'LIKE', '%SF%')
            ->orWhere('position', 'LIKE', '%PF%')
            ->orWhere('position', 'LIKE', '%Small%')
            ->orWhere('position', 'LIKE', '%Power%')
            ->update(['position' => Position::FORWARD]);

        // Update Centers
        DB::table($table)
            ->where('position', 'LIKE', '%Center%')
            ->orWhere('position', 'LIKE', '%center%')
            ->orWhere('position', 'LIKE', '%Centre%')
            ->orWhere('position', 'LIKE', '%centre%')
            ->update(['position' => Position::CENTER]);
    }
};
