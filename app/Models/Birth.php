<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Birth extends Model
{
    protected $table = 'kelahiran';
    protected $primaryKey = 'id_kelahiran';

    protected $fillable = ['id_kelahiran','kode_kelahiran','breeding_id','birth_date','offspring_count', 'notes'];

    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breeding::class, 'breeding_id' , 'id_breeding');
    }

    public function details()
    {
        return $this->hasMany(ItemKelahiran::class, 'kelahiran_id', 'id_kelahiran');
    }

    public function goats()
    {
        return $this->belongsToMany(Goats::class, 'item_kelahiran', 'kelahiran_id', 'product_id');
    }
}