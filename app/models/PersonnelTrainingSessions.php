<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PersonnelTrainingSessions extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'personnel_training_sessions';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function personnel()
    {
        return $this->belongsTo('Personnel');
    }

}