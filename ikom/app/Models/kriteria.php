<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class kriteria extends Model
{
    protected $table = 'kriteria';
    protected $primaryKey = 'fld_krit_id';
    protected $fillable = [
        'fld_krit_id',
        'fld_krit_nama',
        'fld_krit_markah',
    ];

    // Hubungan dengan model Subkriteria
    public function subkriteria()
    {
        return $this->hasMany(subkriteria::class, 'fld_krit_id', 'fld_krit_id');
    }

    // Hubungan dengan model Penilaian
    public function penilaian()
    {
        return $this->hasMany(penilaian::class, 'fld_krit_id', 'fld_krit_id');
    }
}
