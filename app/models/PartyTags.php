<?php

class PartyTags extends Eloquent {

    protected $table = 'party_tag';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function party()
    {
        return $this->belongsTo('Party');
    }

}