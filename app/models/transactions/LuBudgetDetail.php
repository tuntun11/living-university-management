<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class LuBudgetDetail extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'lu_budget_details';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function party()
    {
        return $this->belongsTo('Party', 'party_id');
    }

    public function paidLocation()
    {
        return $this->belongsTo('LuScheduleTaskLocation', 'lu_schedule_task_location_id');
    }

}