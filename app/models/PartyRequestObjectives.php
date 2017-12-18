<?php

class PartyRequestObjectives extends Eloquent {

    protected $table = 'party_request_objectives';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function party()
    {
        return $this->belongsTo('Party');
    }

    public function objective()
    {
        return $this->belongsTo('PartyObjective');
    }
}