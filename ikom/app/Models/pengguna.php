<?php

namespace App\Models;

// TUKAR: Buang 'use Illuminate\Database\Eloquent\Model;'
use Illuminate\Foundation\Auth\User as Authenticatable; 

// TUKAR: extends Authenticatable
class pengguna extends Authenticatable 
{
    protected $table = 'pengguna';  
    protected $primaryKey = 'fld_user_id'; 
    public $timestamps = false;
    
    // TAMBAH: Wajib letak supaya ID '001' tidak bertukar jadi nombor 1
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'fld_user_nama',
        'fld_user_email',
        'fld_user_pass',
        'fld_user_role',
    ];

    // --- HUBUNGAN ---
    public function pelajar() {
        return $this->hasOne(pelajar::class, 'fld_user_id', 'fld_user_id');
    }

    public function penyelarassig() {
        return $this->hasOne(penyelarassig::class, 'fld_user_id', 'fld_user_id');
    }

    public function penyelaraskursus() {
        return $this->hasOne(penyelaraskursus::class, 'fld_user_id', 'fld_user_id');
    }

    // --- FUNGSI WAJIB UNTUK AUTH ---
    public function getAuthPassword() {
        return $this->fld_user_pass;
    }

    public function getEmailForPasswordReset()
    {
        return $this->fld_user_email; // Gunakan nama lajur pangkalan data anda yang sebenar
    }

    public function getAuthIdentifierName()
    {
        return 'fld_user_id'; // PK
    }

    public function getAuthIdentifier()
    {
        return $this->fld_user_id;
    }

}