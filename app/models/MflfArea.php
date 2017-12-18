<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class MflfArea extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'mflf_areas';
    protected $fillable = ['name', 'country', 'province', 'city', 'district', 'geo'];
    protected $guarded = ['id'];

    //area bases for study visit
    public function visitBases()
    {
        return $this->hasMany('LuLocationBases');
    }

}