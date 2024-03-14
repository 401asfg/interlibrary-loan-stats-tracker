<?php

/*
 * Author: Michael Allan
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Library extends Model
{
    protected $table = 'libraries';

    protected $fillable = [
        'name'
    ];
}
