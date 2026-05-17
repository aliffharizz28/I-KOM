<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendaftaranPelajar extends Model
{
    protected $table = 'pendaftaran_pelajar';
    protected $primaryKey = 'fld_daftar_id';

    protected $fillable = [
        'fld_pel_nomat',
        'fld_krs_id',
        'fld_sig_id',
    ];

    // The student's permanent profile
    public function pelajar()
    {
        return $this->belongsTo(pelajar::class, 'fld_pel_nomat', 'fld_pel_nomat');
    }

    // The course session this enrollment belongs to
    public function kursus()
    {
        return $this->belongsTo(kursus::class, 'fld_krs_id', 'fld_krs_id');
    }

    // The SIG the student joined this session
    public function sig()
    {
        return $this->belongsTo(sig::class, 'fld_sig_id', 'fld_sig_id');
    }
}
