<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class subkriteria extends Model
{
    protected $table = 'subkriteria';
    protected $primaryKey = 'fld_sub_id';
    protected $fillable = [
        'fld_sub_id',
        'fld_sub_nama',
        'fld_sub_markah',
        'fld_krit_id',
    ];

    // Hubungan dengan model Kriteria
    public function kriteria()
    {
        return $this->belongsTo(kriteria::class, 'fld_krit_id', 'fld_krit_id');
    }

    // Hubungan dengan model Penilaian
    public function penilaian()
    {
        return $this->hasMany(penilaian::class, 'fld_sub_id', 'fld_sub_id');
    }

    // Hubungan dengan model SubkriteriaDesc
    public function descriptions()
    {
        return $this->hasMany(subkriteriaDesc::class, 'fld_sub_id', 'fld_sub_id');
    }
}
