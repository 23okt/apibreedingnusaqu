<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    protected $primaryKey = 'id_transaksi';
    protected $table = 'transaksi';
    protected $fillable = ['id_transaksi','kode_transaksi','nama_pembeli','tanggal_transaksi','bukti_pembayaran', 'status_pembayaran', 'jumlah_nominal', 'jumlah_nominal_terbilang'];

    public function itemTransaksi()
    {
        return $this->hasMany(ItemTransaksi::class, 'transaksi_id', 'id_transaksi');
    }
}