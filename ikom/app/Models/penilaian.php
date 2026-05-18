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
        'fld_sig_id',
        'fld_krs_id',
        'fld_pel_nomat',
        'fld_krit_id',
        'fld_markah_detail',
    ];

    protected $casts = [
        'fld_markah_detail' => 'array',
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
}
