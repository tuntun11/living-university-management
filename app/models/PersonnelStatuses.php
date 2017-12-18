<?php

class PersonnelStatuses extends Eloquent {

    protected $table = 'personnel_statuses';
    protected $fillable = ['status'];
    protected $guarded = ['id'];

    public function personnel()
    {
        return $this->belongsTo('Personnel');
    }
}