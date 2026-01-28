<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penanganan extends Model
{
    use HasFactory;

    protected $table = 'penanganan';
    protected $primaryKey = 'id_penanganan';
    protected $fillable = ['kesehatan_id', 'kode_penanganan', 'judul_penanganan', 'catatan_penanganan', 'tanggal_penanganan'];

    public function kesehatan(): BelongsTo
    {
        return $this->belongsTo(HealthRecord::class, 'kesehatan_id', 'id_kesehatan');
    }

    public function items()
    {
        return $this->hasMany(ItemPenanganan::class, 'penanganan_id', 'id_penanganan');
    }

}