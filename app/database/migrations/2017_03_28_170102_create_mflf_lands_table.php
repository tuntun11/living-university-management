<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMflfLandsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mflf_lands', function($table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('country', 3)->default('th');
			$table->integer('province')->nullable();
			$table->integer('created_by');
			$table->integer('updated_by');
			$table->timestamps();
			$table->softDeletes();
		});

		Schema::table('parties', function($table)
		{
			$table->dropColumn(array('transportation_detail', 'meal_detail'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
