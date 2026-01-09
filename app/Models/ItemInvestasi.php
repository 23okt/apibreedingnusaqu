<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemInvestasi extends Model
{
    use HasFactory;
    protected $table = 'item_investment';
    protected $primaryKey = 'id_item_invest';

    protected $fillable = ['investasi_id','product_id','jumlah_investasi'];

    public function inves()
    {
        return $this->hasMany(Investment::class, 'investasi_id', 'id_investasi');
    }

    public function product()
    {
        return $this->belongsTo(Goats::class, 'product_id', 'id_product');
    }
}