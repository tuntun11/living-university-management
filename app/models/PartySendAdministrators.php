<?php

class PartySendAdministrators extends Eloquent {

    protected $table = 'party_send_administrators';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function party()
    {
        return $this->belongsTo('Party');
    }

}