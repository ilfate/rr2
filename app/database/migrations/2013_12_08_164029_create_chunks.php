<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateChunks extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('chunks', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('battle_map_id')->unsigned()->index();
			$table->integer('x');
			$table->integer('y');
			$table->string('cells', 200);
			$table->integer('biom_id');

            $table->foreign('battle_map_id')->references('id')->on('battle_maps')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('chunks');
	}

}
