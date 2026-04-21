<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable; // Import Authenticatable

class pelajar extends Authenticatable
{
    protected $table = 'pelajar'; // Tetapkan nama table
    protected $primaryKey = 'fld_pel_nomat'; // Tetapkan primary key
    public $incrementing = false; // Non-incrementing primary key
    protected $fillable = [
        'fld_pel_nomat',
        'fld_pel_tahun',
        'fld_pel_jurusan',
        'fld_user_id', // FK ke jadual Pengguna
        'fld_sig_id', // FK ke jadual SIG
        'fld_pel_pic',
    ];

    // Hubungan dengan model Pengguna
    public function pengguna()
    {
        return $this->belongsTo(pengguna::class, 'fld_user_id', 'fld_user_id');
    }

    // Hubungan dengan model SIG
    public function sig()
    {
        return $this->belongsTo(sig::class, 'fld_sig_id', 'fld_sig_id');
    }

    // Hubungan dengan model Majlis Tertinggi
    public function majlistertinggi()
    {
        return $this->hasOne(majlistertinggi::class, 'fld_pel_nomat', 'fld_pel_nomat');
    }

    // Hubungan dengan model Perjumpaan
    public function perjumpaan()
    {
        return $this->hasMany(perjumpaan::class, 'fld_pel_nomat', 'fld_pel_nomat');
    }

    // Hubungan dengan model Kehadiran
    public function kehadiran()
    {
        return $this->hasMany(kehadiran::class, 'fld_pel_nomat', 'fld_pel_nomat');
    }

    // Hubungan dengan model Penghantaran
    public function penghantaran()
    {
        return $this->hasMany(penghantaran::class, 'fld_pel_nomat', 'fld_pel_nomat');
    }

    // Hubungan dengan model Penilaian
    public function penilaian()
    {
        return $this->hasMany(penilaian::class, 'fld_pel_nomat', 'fld_pel_nomat');
    }
}
