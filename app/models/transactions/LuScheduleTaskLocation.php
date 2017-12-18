<?php

class LuScheduleTaskLocation extends Eloquent {

    protected $table = 'lu_schedule_task_locations';
    protected $fillable = ['lu_schedule_task_id', 'location_id'];
    protected $guarded = ['id'];

    public function task()
    {
        return $this->belongsTo('LuScheduleTask', 'lu_schedule_task_id');
    }

    public function location()
    {
        return $this->belongsTo('Location', 'location_id');
    }

    public function expenseTransactions()
    {
        return $this->hasMany('LuBudgetDetail', 'lu_schedule_task_location_id');
    }

}