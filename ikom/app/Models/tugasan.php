<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tugasan extends Model
{
    protected $table = 'tugasan';
    protected $primaryKey = 'fld_tgs_id';
    protected $fillable = [
        'fld_tgs_id',
        'fld_tgs_nama',
        'fld_tgs_desc',
        'fld_tgs_tarikh',
        'fld_tgs_status',
        'fld_sig_id',
        'fld_tgs_file',
    ];

    // Hubungan dengan model SIG
    public function sig()
    {
        return $this->belongsTo(sig::class, 'fld_sig_id', 'fld_sig_id');
    }

    // Hubungan dengan model Penghantaran
    public function penghantaran()
    {
        return $this->hasMany(penghantaran::class, 'fld_tgs_id', 'fld_tgs_id');
    }
}
