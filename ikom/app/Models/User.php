<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'pengguna';

    protected $primaryKey = 'fld_user_id';

    public $timestamps = false;

    protected $fillable = [
        'fld_user_email',
        'fld_user_pass'
    ];

    protected $hidden = [
        'fld_user_pass'
    ];
}