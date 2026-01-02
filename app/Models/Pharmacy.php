<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pharmacy extends Model
{
    use HasFactory;

    protected $table = 'obat';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $primaryKey = 'id_obat';

    protected $fillable = ['kode_obat','nama_obat', 'type_obat', 'stock_obat', 'isi_obat','total_obat'];

    public function penanganan(): BelongsToMany{
        return $this->belongsToMany(Penanganan::class, 'item_penanganan', 'obat_id', 'penanganan_id')
                    ->withPivot('jumlah_terpakai');
    }
}