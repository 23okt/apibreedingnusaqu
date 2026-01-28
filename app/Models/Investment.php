<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investment extends Model
{

    use HasFactory;
    protected $table = 'investasi';
    protected $primaryKey = 'id_investasi';

    protected $fillable = ['id_investasi','kode_investasi','users_id','jumlah_inves', 'jumlah_inves_terbilang','metode_pembayaran','tanggal_investasi','status','description', 'bukti_pembayaran'];

    protected $casts = [
        'tanggal_investasi' => 'date',
    ];

    public function users()
    {
        return $this->belongsTo(Users::class, 'users_id');
    }
    public function products()
    {
        return $this->belongsToMany(
            Goats::class,
            'item_investment',
            'investasi_id',
            'product_id'
        )->withPivot('jumlah_investasi')->withTimestamps();
    }

}