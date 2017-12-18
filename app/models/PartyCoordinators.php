<?php

class PartyCoordinators extends Eloquent {

    protected $table = 'party_coordinators';
    protected $fillable = ['name', 'mobile', 'email'];
    protected $guarded = ['id'];

    public function party()
    {
        return $this->belongsTo('Party');
    }
}