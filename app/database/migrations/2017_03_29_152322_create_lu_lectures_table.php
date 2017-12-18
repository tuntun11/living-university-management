<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLuLecturesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lu_lectures', function($table)
		{
			$table->increments('id');
			$table->integer('lu_schedule_task_location_id')->unsigned();
			$table->integer('personnel_id')->unsigned()->index();
			$table->dateTime('start');
			$table->dateTime('end');
			$table->integer('created_by');
			$table->integer('updated_by');
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('lu_schedule_task_location_id')->references('id')->on('lu_schedule_task_locations');
			$table->foreign('personnel_id')->references('id')->on('personnels');
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
