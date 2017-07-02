<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provident extends Model
{
    protected $dates = ['period_at'];

    public function getUser(){
        return $this->hasOne('App\User','job_number','job_number');
    }
    //
}
