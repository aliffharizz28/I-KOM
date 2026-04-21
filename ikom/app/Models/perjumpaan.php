<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class perjumpaan extends Model
{
    protected $table = 'perjumpaan';
    protected $primaryKey = 'fld_meet_id';
    protected $fillable = [
        'fld_meet_id',
        'fld_meet_topik',
        'fld_meet_tarikh',
        'fld_meet_status',
        'fld_pel_nomat',
        'fld_sig_id',
    ];

    // Hubungan dengan model Pelajar
    public function pelajar()
    {
        return $this->belongsTo(pelajar::class, 'fld_pel_nomat', 'fld_pel_nomat');
    }

    // Hubungan dengan model SIG
    public function sig()
    {
        return $this->belongsTo(sig::class, 'fld_sig_id', 'fld_sig_id');
    }
}
