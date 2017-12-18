<?php

class LuScheduleTask extends Eloquent {

    protected $table = 'lu_schedule_tasks';
    protected $fillable = ['start', 'end', 'type'];
    protected $guarded = ['id'];

    public function schedule()
    {
        return $this->belongsTo('LuSchedule', 'lu_schedule_id');
    }

    public function taskLocations()
    {
        return $this->hasMany('LuScheduleTaskLocation');
    }

}