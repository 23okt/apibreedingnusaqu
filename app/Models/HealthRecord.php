<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthRecord extends Model
{
    protected $keyType = 'int';
    protected $table = 'kesehatan';
    protected $primaryKey = 'id_kesehatan';

    protected $fillable = ['kode_kesehatan','check_date', 'diagnosa','photo1','photo2','photo3','notes','status_kesehatan','product_id'];

    public function product()
    {
        return $this->belongsTo(Goats::class, 'product_id', 'id_product');
    }

    public function penanganan()
    {
        return $this->hasMany(Penanganan::class, 'kesehatan_id', 'id_kesehatan');
    }
}