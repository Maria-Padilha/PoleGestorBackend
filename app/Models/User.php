<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nome',
        'email',
        'senha',
        'cpf_cnpj',
        'telefone',
        'endereco',
        'nascimento',
        'cep',
        'cidade',
        'estado',
        'plano_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function setSenhaAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['senha'] = Hash::make($value);
        }
    }

    public function metodosPagamento()
    {
        return $this->hasMany(MetodosPagamento::class, 'usuario_id');
    }

    public function planoAtual()
    {
        return $this->belongsTo(PlanosModel::class, 'plano_id', 'id');
    }
}
