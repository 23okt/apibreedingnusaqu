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
    protected $fillable = ['id_transaksi','kode_transaksi','product_id','users_id','harga_beli','harga_jual','bobot'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Goats::class);
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(Users::class);
    }
}