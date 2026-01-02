<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pregnant extends Model
{
    use HasFactory;
    protected $table = 'kehamilan';
    protected $primaryKey = 'id_kehamilan';

    protected $fillable = ['kode_kehamilan','breeding_id','check_date','status', 'notes', 'photos'];

    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breeding::class, 'breeding_id', 'id_breeding');
    }
}