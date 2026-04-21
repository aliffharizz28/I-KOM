<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class penyelaraskursus extends Model
{
    protected $table = 'penyelaraskursus';
    protected $primaryKey = 'fld_pk_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'fld_pk_id',
        'fld_user_id',
    ];

    // Hubungan dengan model Pengguna
    public function pengguna()
    {
        return $this->belongsTo(pengguna::class, 'fld_user_id', 'fld_user_id');
    }
}
