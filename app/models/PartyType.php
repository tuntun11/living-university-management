<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PartyType extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'party_type';
    protected $fillable = ['name'];
    protected $guarded = ['id'];

    public function party()
    {
        return $this->hasOne('Party');
    }
}