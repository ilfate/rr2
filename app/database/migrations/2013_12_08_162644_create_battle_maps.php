<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBattleMaps extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('battle_maps', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('map_id')->unsigned()->index();
			$table->integer('time')->unsigned()->default(0);
			$table->boolean('active')->default(true);

			$table->timestamps();
            $table->foreign('map_id')->references('id')->on('maps')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('battle_maps');
	}

}
