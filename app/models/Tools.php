<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Tools extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'tools';
    protected $fillable = ['name'];
    protected $guarded = ['id'];

}