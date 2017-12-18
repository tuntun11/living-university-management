<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PartyObjective extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'party_objective';
	protected $fillable = ['name'];
    protected $guarded = ['id'];

    public function requestObjectives()
    {
        return $this->hasMany('PartyRequestObjectives', 'party_objective_id');
    }
}