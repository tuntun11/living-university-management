<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class MflfLocation extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'mflf_locations';
    protected $fillable = [];
    protected $guarded = ['id'];

}