<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class penyelarassig extends Model
{
    protected $table = 'penyelarassig';
    protected $primaryKey = 'fld_penyelaras_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'fld_penyelaras_id',
        'fld_user_id',
        'fld_sig_id',
    ];

    // Hubungan dengan model Pengguna
    public function pengguna()
    {
        return $this->belongsTo(pengguna::class, 'fld_user_id', 'fld_user_id');
    }

    // Hubungan dengan model SIG
    public function sig()
    {
        return $this->belongsTo(sig::class, 'fld_sig_id', 'fld_sig_id');
    }
}
