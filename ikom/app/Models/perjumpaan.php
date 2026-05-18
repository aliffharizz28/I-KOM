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
        'fld_meet_verify',
        'fld_sig_id',
        'fld_krs_id',
    ];

    // Hubungan dengan model SIG
    public function sig()
    {
        return $this->belongsTo(sig::class, 'fld_sig_id', 'fld_sig_id');
    }

    public function kehadiran() {
        return $this->hasMany(kehadiran::class);
    }


}
