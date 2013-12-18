<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBattleLogs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('battle_logs', function(Blueprint $table) {
			$table->integer('user_id')->unsigned();
			$table->integer('battle_map_id')->unsigned();
			$table->integer('time');
			$table->boolean('is_watched')->default(false);
			$table->text('data');

			$table->timestamps();
            $table->index(['user_id', 'battle_map_id', 'time'], 'IDX_USER_ID_TIME');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('battle_logs');
	}

}
