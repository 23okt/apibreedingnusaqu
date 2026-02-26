<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ItemTransaksi extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_item_transaksi';
    protected $table = 'item_transaksi';
    protected $fillable = ['transaksi_id', 'product_id', 'harga_beli', 'harga_jual', 'bobot'];

    public function transaksi()
    {
        return $this->belongsTo(ItemTransaksi::class, 'id_transaksi', 'transaksi_id');
    }

    public function product()
    {
        return $this->belongsTo(Goats::class, 'product_id', 'id_product');
    }
}