<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemKelahiran extends Model
{
    protected $table = 'item_kelahiran';
    protected $primaryKey = 'id_item_kelahiran';

    protected $fillable = [
        'kelahiran_id',
        'product_id'
    ];

    public function birth(): BelongsTo
    {
        return $this->belongsTo(Birth::class, 'kelahiran_id', 'id_kelahiran');
    }

    public function goat(): BelongsTo
    {
        return $this->belongsTo(Goats::class, 'product_id', 'id_product');
    }
}