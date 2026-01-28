<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Breeding extends Model
{

    use HasFactory;

    protected $incremental = true;
    protected $table = 'perkawinan';
    protected $primaryKey = 'id_breeding';

    protected $fillable = ['id_breeding','kode_breeding','female_id', 'male_id','tanggal_pkb','status','notes'];

    public function pregnant()
    {
        return $this->hasMany(Pregnant::class, 'breeding_id','id_breeding');
    }

    public function birth()
    {
        return $this->hasMany(Birth::class, 'breeding_id','id_breeding');
    }

    public function female()
    {
        return $this->belongsTo(Goats::class, 'female_id' , 'id_product');
    }

    public function male()
    {
        return $this->belongsTo(Goats::class, 'male_id' , 'id_product');
    }
}