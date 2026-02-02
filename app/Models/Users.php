<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Users extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $keyType = 'int';
    protected $primaryKey = 'id_users';
    protected $fillable = ['id_users','kode_unik','nama_users', 'pass_users','alamat','no_telp','role','status'];

    public function invest()
    {
        return $this->hasMany(Investment::class, 'users_id', 'id_users');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }

    public function product()
    {
        return $this->hasOne(Goats::class);
    }

    public function getAuthPassword()
    {
        return $this->pass_users;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}