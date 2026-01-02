<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timbangan extends Model
{

    protected $table = 'timbangan';
    protected $primaryKey = 'id_timbangan';
    protected $fillable = ['id_timbangan','kode_timbangan','product_id','bobot', 'tanggal'];

    public function goats(): BelongsTo
    {
        return $this->belongsTo(Goats::class);
    }
}