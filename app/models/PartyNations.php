<?php

class PartyNations extends Eloquent {

    protected $table = 'party_nations';
    protected $fillable = ['country'];
    protected $guarded = ['id'];

    public function party()
    {
        return $this->belongsTo('Party');
    }

}