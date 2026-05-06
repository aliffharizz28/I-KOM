<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class subkriteriaDesc extends Model
{
    protected $table = 'subkriteria_desc';
    protected $primaryKey = 'fld_desc_id';
    protected $fillable = [
        'fld_sub_id',
        'fld_desc_text',
        'fld_desc_markah',
    ];

    // Relationship to parent subkriteria
    public function subkriteria()
    {
        return $this->belongsTo(subkriteria::class, 'fld_sub_id', 'fld_sub_id');
    }
}
