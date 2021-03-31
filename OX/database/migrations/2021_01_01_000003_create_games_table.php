<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->integer('map_width')->default(12);
            $table->integer('map_height')->default(12);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('first_player')->nullable();
            $table->unsignedBigInteger('second_player')->nullable();
            $table->unsignedBigInteger('latest_player')->nullable();
            $table->char('first_symbol')->default('O');
            $table->char('second_symbol')->default('X');
            $table->enum('status', ['WAITING', 'ABANDONED', 'STARTED', 'ENDED'])->default('WAITING');
            $table->unsignedBigInteger('winned_by')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('first_player')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('second_player')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('latest_player')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('winned_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games');
    }
}
