<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    //
    const STATUS_DRAFT   = 0;
    const STATUS_PENDING = 1;
    const STATUS_RETIRED = 11;
    const STATUS_PASSED  = 21;
}
