<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class penghantaran extends Model
{
    protected $table = 'penghantaran';
    protected $primaryKey = 'fld_pgh_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'fld_pgh_id',
        'fld_pgh_fail',
        'fld_pel_nomat',
        'fld_tgs_id',
    ];

    // Hubungan dengan model Pelajar
    public function pelajar()
    {
        return $this->belongsTo(pelajar::class, 'fld_pel_nomat', 'fld_pel_nomat');
    }

    // Hubungan dengan model Tugasan
    public function tugasan()
    {
        return $this->belongsTo(tugasan::class, 'fld_tgs_id', 'fld_tgs_id');
    }
}
