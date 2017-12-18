<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PersonnelClassroomBases extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'personnel_classroom_bases';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function location()
    {
        return $this->belongsTo('Location');
    }

    public function personnel()
    {
        return $this->belongsTo('Personnel');
    }

}