<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PersonnelFiles extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'personnel_files';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function personnel()
    {
        return $this->belongsTo('Personnel');
    }

}