<?php

class LuSchedule extends Eloquent {

    protected $table = 'lu_schedules';
    protected $fillable = ['party_id'];
    protected $guarded = ['id'];

    public function party()
    {
        return $this->belongsTo('Party', 'party_id');
    }

    public function tasks()
    {
        return $this->hasMany('LuScheduleTask');
    }

}