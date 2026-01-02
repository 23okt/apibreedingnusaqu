<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;



class Goats extends Model
{
    //
    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'product';
    protected $primaryKey = 'id_product';

    protected $fillable = ['id_product','kode_product','nama_product', 'jenis_product','type_product', 'gender', 'birth_date', 'harga_jual','harga_beli','bobot', 'photo1','photo2','photo3','status', 'mother_id', 'father_id', 'users_id', 'kandang_id'];

    public function cage()
    {
        return $this->belongsTo(Cage::class. 'kandang_id', 'id_kandang');
    }

    public function users()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function mother()
    {
        return $this->belongsTo(Goats::class, 'mother_id', 'id_product');
    }

    public function father()
    {
        return $this->belongsTo(Goats::class, 'father_id', 'id_product');
    }

    public function children()
    {
        return $this->hasMany(Goats::class, 'mother_id', 'id_product');
    }

    public function breedingAsFemale()
    {
        return $this->hasMany(Breeding::class, 'female_id', 'id_product');
    }

    public function breedingAsMale()
    {
        return $this->hasMany(Breeding::class, 'male_id', 'id_product');
    }

    public function timbangan()
    {
        return $this->hasMany(Timbangan::class, 'product_id', 'id_product');
    }

    public function health()
    {
        return $this->hasMany(HealthRecord::class, 'product_id', 'id_product')
            ->orderByDesc('check_date');
    }

    public function investments()
    {
        return $this->belongsToMany(
            Investment::class,
            'item_investment',
            'product_id',
            'investasi_id'
        )->withPivot('jumlah_investasi')->withTimestamps();
    }

    public function totalInvestment()
    {
        return $this->investments()
            ->selectRaw('SUM(item_investment.jumlah_investasi) as total')
            ->value('total');
    }
}