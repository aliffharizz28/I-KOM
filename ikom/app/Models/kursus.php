<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class kursus extends Model
{
    protected $table = 'kursus';
    protected $primaryKey = 'fld_krs_id';
    protected $fillable = [
        'fld_krs_id',
        'fld_krs_nama',
        'fld_krs_semester',
        'fld_krs_tahun',
    ];
    public $timestamps = false;


    // Hubungan dengan model Penyelaras Kursus
    public function penyelaraskursus()
    {
        return $this->hasMany(penyelaraskursus::class, 'fld_krs_id', 'fld_krs_id');
    }
}
