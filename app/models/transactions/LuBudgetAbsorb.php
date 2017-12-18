<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class LuBudgetAbsorb extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'lu_budget_absorbs';
    protected $fillable = [];
    protected $guarded = ['id'];
    //currently is not used;
}