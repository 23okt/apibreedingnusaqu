<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Birth extends Model
{
    protected $table = 'kelahiran';
    protected $primaryKey = 'id_kelahiran';

    protected $fillable = ['id_kelahiran','kode_kelahiran','breeding_id','birth_date','offspring_count', 'photos', 'notes'];

    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breeding::class, 'breeding_id' , 'id_breeding');
    }
}