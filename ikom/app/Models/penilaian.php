<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class penilaian extends Model
{
    protected $table = 'penilaian';
    protected $primaryKey = 'fld_nilai_id';
    protected $fillable = [
        'fld_nilai_id',
        'fld_nilai_markah',
        'fld_nilai_gred',
        'fld_nilai_komen',
        'fld_sig_id',
        'fld_pel_nomat',
        'fld_krit_id',
        'fld_sub_id',
    ];

    // Hubungan dengan model SIG
    public function sig()
    {
        return $this->belongsTo(sig::class, 'fld_sig_id', 'fld_sig_id');
    }

    // Hubungan dengan model Pelajar
    public function pelajar()
    {
        return $this->belongsTo(pelajar::class, 'fld_pel_nomat', 'fld_pel_nomat');
    }

    // Hubungan dengan model Kriteria
    public function kriteria()
    {
        return $this->belongsTo(kriteria::class, 'fld_krit_id', 'fld_krit_id');
    }

    // Hubungan dengan model Subkriteria
    public function subkriteria()
    {
        return $this->belongsTo(subkriteria::class, 'fld_sub_id', 'fld_sub_id');
    }
}
