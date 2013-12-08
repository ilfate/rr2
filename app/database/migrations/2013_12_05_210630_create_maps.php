<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMaps extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('maps', function(Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->integer('width');
			$table->integer('height');
			$table->integer('chunk_size')->default(10);
			$table->integer('max_players')->default(100);
			$table->boolean('is_pvp')->default(false);
			$table->string('bioms');
			$table->enum('type', array('normal', 'training', 'deathmatch', 'dungeon'))->default('normal');
			$table->enum('spawn', array('random', 'specific', 'radius'))->default('random');
            $table->text('configuration')->default('');
            $table->boolean('active')->default(true);

			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('maps');
	}

}
