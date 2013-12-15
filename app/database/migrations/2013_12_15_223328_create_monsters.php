<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMonsters extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('monsters', function(Blueprint $table) {
			$table->integer('id');
			$table->integer('battle_map_id')->unsigned()->index();
			$table->integer('level');
			$table->integer('health');
			$table->string('type', 20);
			$table->string('data');
            $table->unique('id', 'battle_map_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('monsters');
	}

}
