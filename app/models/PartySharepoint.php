<?php

class PartySharepoint extends Eloquent {

    protected $table = 'party_sharepoint';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function party()
    {
        return $this->belongsTo('Party');
    }

}