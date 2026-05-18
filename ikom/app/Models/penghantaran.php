<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class penghantaran extends Model
{
    protected $table = 'penghantaran';
    protected $primaryKey = 'fld_pgh_id';
    public $incrementing = false;       // string PK, not auto-increment
    protected $keyType = 'string';      // match migration: string('fld_pgh_id')
    protected $fillable = [
        'fld_pgh_id',
        'fld_pgh_fail',
        'fld_pel_nomat',
        'fld_tgs_id',
    ];

    // Auto-generate UUID for string primary key
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->fld_pgh_id)) {
                $model->fld_pgh_id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

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
