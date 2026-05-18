<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class keputusan extends Model
{
    use HasFactory;

    protected $table = 'keputusan';
    protected $primaryKey = 'fld_keputusan_id';

    protected $fillable = [
        'fld_pel_nomat',
        'fld_total_markah',
        'fld_nilai_gred',
        'fld_nilai_komen',
        'fld_sig_id',
        'fld_krs_id',
    ];

    public function pelajar()
    {
        return $this->belongsTo(pelajar::class, 'fld_pel_nomat', 'fld_pel_nomat');
    }

    public function sig()
    {
        return $this->belongsTo(sig::class, 'fld_sig_id', 'fld_sig_id');
    }
}
