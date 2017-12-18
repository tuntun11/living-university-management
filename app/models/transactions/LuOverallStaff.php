<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class LuOverallStaff extends Eloquent {
	
	use SoftDeletingTrait;

    protected $table = 'lu_overall_staffs';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function party()
    {
        return $this->belongsTo('Party', 'party_id');
    }
	
	public function personnel()
    {
        return $this->belongsTo('Personnel', 'personnel_id');
    }

    public function works()
    {
        return $this->hasMany('LuOverallStaffWork', 'overall_staff_id');
    }

}