<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SigSubkriteria extends Model
{
    protected $table = 'sig_subkriteria';
    protected $primaryKey = 'fld_sigsub_id';

    protected $fillable = [
        'fld_sig_id',
        'fld_krit_id',
        'fld_sub_id',
        'fld_sub_markah',
    ];

    public function sig()
    {
        return $this->belongsTo(sig::class, 'fld_sig_id', 'fld_sig_id');
    }

    public function kriteria()
    {
        return $this->belongsTo(kriteria::class, 'fld_krit_id', 'fld_krit_id');
    }

    public function subkriteria()
    {
        return $this->belongsTo(subkriteria::class, 'fld_sub_id', 'fld_sub_id');
    }
}
