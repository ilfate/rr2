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
			$table->integer('id');
			$table->integer('battle_map_id')->unsigned()->index();
			$table->integer('x')->unsigned();
			$table->integer('y')->unsigned();
			$table->string('cells', 200);
			$table->integer('biom_id')->unsigned();

            $table->foreign('battle_map_id')->references('id')->on('battle_maps')->onDelete('cascade');
            $table->index(array('id', 'battle_map_id'), 'IDX_ID_MAP_ID');
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
