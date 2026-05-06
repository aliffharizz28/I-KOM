<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class kehadiran extends Model
{
    protected $table = 'kehadiran';
    protected $primaryKey = 'fld_hdr_id';
    protected $fillable = [
        'fld_hdr_id',
        'fld_hdr_status',
        'fld_meet_id',
        'fld_pel_nomat',
    ];

    // Hubungan dengan model Perjumpaan
    public function perjumpaan()
    {
        return $this->belongsTo(perjumpaan::class, 'fld_meet_id', 'fld_meet_id');
    }

    // Hubungan dengan model Pelajar
    public function pelajar()
    {
        return $this->belongsTo(pelajar::class, 'fld_pel_nomat', 'fld_pel_nomat');
    }
}
