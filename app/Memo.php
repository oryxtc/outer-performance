<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    //
    public function getUser(){
        return $this->hasOne('App\User','job_number','job_number');
    }
}
