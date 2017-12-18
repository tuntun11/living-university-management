<?php

class PersonnelUser extends Eloquent {

    protected $table = 'personnel_users';
    protected $fillable = [];
    protected $guarded = ['personnel_id', 'user_id'];

    public function user()
    {
        return $this->hasOne('User');
    }

    public function personnel()
    {
        return $this->hasOne('Personnel');
    }

}