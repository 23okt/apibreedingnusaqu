<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cage extends Model
{

    use HasFactory;
    
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'kandang';
    protected $primaryKey = 'id_kandang';

    protected $fillable =['id_kandang','kode_kandang','nama_kandang', 'type_kandang', 'jumlah_kambing', 'lokasi'];

    public function goats(): HasMany
    {
        return $this->hasMany(Goats::class);
    }
}