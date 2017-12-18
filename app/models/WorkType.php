<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class WorkType extends Eloquent {
	
	use SoftDeletingTrait;

    protected $table = 'work_types';
    protected $fillable = ['name'];
    protected $guarded = ['id'];

}