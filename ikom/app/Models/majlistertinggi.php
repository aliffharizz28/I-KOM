<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class majlistertinggi extends Model
{
    protected $table = 'majlistertinggi';
    protected $primaryKey = 'fld_mt_id';
    protected $fillable = [
        'fld_mt_id',
        'fld_mt_jawatan',
        'fld_pel_nomat',
    ];

    // Hubungan dengan model Pelajar
    public function pelajar()
    {
        return $this->belongsTo(pelajar::class, 'fld_pel_nomat', 'fld_pel_nomat');
    }
}
