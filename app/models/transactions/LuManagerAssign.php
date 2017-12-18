<?php

class LuManagerAssign extends Eloquent {

    protected $table = 'lu_manager_assign';
    protected $fillable = ['coordinator_assigned'];
    protected $guarded = ['id'];

    public function party()
    {
        return $this->hasOne('Party', 'id');
    }

    public function personnel()
    {
        return $this->hasOne('Personnel', 'id');
    }

}