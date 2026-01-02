<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    protected $keyType = 'string';
    protected $table = 'supplier';
    protected $primaryKey = 'id_supplier';

    protected $fillable = ['id_supplier','kode_supplier','nama_supplier','no_supplier'];
}