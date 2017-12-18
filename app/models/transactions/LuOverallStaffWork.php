<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class LuOverallStaffWork extends Eloquent {
	
	use SoftDeletingTrait;

    protected $table = 'lu_overall_staff_works';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function staff()
    {
        return $this->belongsTo('LuOverallStaff', 'overall_staff_id');
    }
	
}