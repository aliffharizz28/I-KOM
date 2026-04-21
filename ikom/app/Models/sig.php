<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sig extends Model
{
    protected $table = 'sig';
    protected $primaryKey = 'fld_sig_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'fld_sig_id',
        'fld_sig_nama',
    ];

    // Hubungan dengan model lain
    public function pelajar()
    {
        return $this->hasMany(pelajar::class, 'fld_sig_id', 'fld_sig_id');
    }

    public function penyelarassig()
    {
        return $this->hasMany(penyelarassig::class, 'fld_sig_id', 'fld_sig_id');
    }

    public function perjumpaan()
    {
        return $this->hasMany(perjumpaan::class, 'fld_sig_id', 'fld_sig_id');
    }

    public function tugasan()
    {
        return $this->hasMany(tugasan::class, 'fld_sig_id', 'fld_sig_id');
    }

    public function penilaian()
    {
        return $this->hasMany(penilaian::class, 'fld_sig_id', 'fld_sig_id');
    }
}
