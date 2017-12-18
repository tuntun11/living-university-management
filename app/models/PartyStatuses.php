<?php

class PartyStatuses extends Eloquent {

    protected $table = 'party_statuses';
    protected $fillable = ['status'];
    protected $guarded = ['id'];

    public function party()
    {
        return $this->belongsTo('Party');
    }
}