<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemPenanganan extends Model
{
    use HasFactory;
    protected $table = 'item_penanganan';
    protected $fillable = ['penanganan_id', 'obat_id', 'jumlah_terpakai'];

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'obat_id', 'id_obat');
    }

    public function penanganan()
    {
        return $this->belongsTo(Penanganan::class, 'penanganan_id', 'id_penanganan');
    }
}