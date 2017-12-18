<?php

class LuBudget extends Eloquent {

    protected $table = 'lu_budgets';
    protected $fillable = ['type', 'plan'];
    protected $guarded = ['id'];

    public function party()
    {
        return $this->belongsTo('Party', 'party_id');
    }

}