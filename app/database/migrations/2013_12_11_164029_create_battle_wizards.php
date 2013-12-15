<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBattleWizards extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('battle_wizards', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('battle_map_id')->unsigned()->index();
			$table->integer('wizard_id')->unsigned();
			$table->integer('chunk_id')->unsigned();
            $table->text('data');
            $table->timestamps();

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
