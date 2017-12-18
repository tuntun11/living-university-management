<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class SpecialEvents extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'special_events';
    protected $fillable = ['name'];
    protected $guarded = ['id'];

}