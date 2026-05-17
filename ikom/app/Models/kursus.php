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
        'fld_krs_aktif',
    ];
    protected $casts = [
        'fld_krs_aktif' => 'boolean',
    ];
    public $timestamps = false;

    // Get the currently active course session (used app-wide)
    public static function getActive(): ?self
    {
        return static::where('fld_krs_aktif', true)->first();
    }

    // Hubungan dengan model Penyelaras Kursus
    public function penyelaraskursus()
    {
        return $this->hasMany(penyelaraskursus::class, 'fld_krs_id', 'fld_krs_id');
    }

    // Hubungan dengan enrollment pelajar bagi sesi ini
    public function pendaftaranPelajar()
    {
        return $this->hasMany(PendaftaranPelajar::class, 'fld_krs_id', 'fld_krs_id');
    }
}
