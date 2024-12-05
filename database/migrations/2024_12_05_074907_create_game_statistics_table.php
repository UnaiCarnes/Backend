<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('game_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('player_id')->unique();
            $table->decimal('balance', 10, 2)->default(0);
            $table->integer('games_played')->default(0);
            $table->string('most_played_game')->nullable();
            $table->integer('games_won')->default(0);
            $table->integer('games_lost')->default(0);
            $table->string('win_rate')->default('0%');
            $table->decimal('average_bet', 10, 2)->default(0);
            $table->decimal('total_winnings', 10, 2)->default(0);
            $table->decimal('total_losses', 10, 2)->default(0);
            $table->decimal('last_prize', 10, 2)->default(0);
            $table->decimal('best_prize', 10, 2)->default(0);
            $table->decimal('highest_bet', 10, 2)->default(0);
            $table->integer('highest_streak')->default(0);
            $table->integer('alcoholic_drink')->default(0);
            $table->integer('hydrating_drink')->default(0);
            $table->integer('toxic_substances')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('game_statistics');
    }
};