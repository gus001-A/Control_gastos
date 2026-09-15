<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    public const DEFAULT = [
        ['nombre' => 'Comida y despensa', 'tipo' => 'gasto', 'color' => '#f97316', 'sistema' => false],
        ['nombre' => 'Transporte', 'tipo' => 'gasto', 'color' => '#0ea5e9', 'sistema' => false],
        ['nombre' => 'Renta / Hogar', 'tipo' => 'gasto', 'color' => '#a855f7', 'sistema' => false],
        ['nombre' => 'Servicios (luz, agua, internet)', 'tipo' => 'gasto', 'color' => '#eab308', 'sistema' => false],
        ['nombre' => 'Salud', 'tipo' => 'gasto', 'color' => '#ef4444', 'sistema' => false],
        ['nombre' => 'Entretenimiento', 'tipo' => 'gasto', 'color' => '#ec4899', 'sistema' => false],
        ['nombre' => 'Educación', 'tipo' => 'gasto', 'color' => '#06b6d4', 'sistema' => false],
        ['nombre' => 'Otros gastos', 'tipo' => 'gasto', 'color' => '#64748b', 'sistema' => false],
        ['nombre' => 'Pago de deudas', 'tipo' => 'gasto', 'color' => '#dc2626', 'sistema' => true],
        ['nombre' => 'Sueldo / Nómina', 'tipo' => 'ingreso', 'color' => '#16a34a', 'sistema' => false],
        ['nombre' => 'Ingreso freelance', 'tipo' => 'ingreso', 'color' => '#22c55e', 'sistema' => true],
        ['nombre' => 'Ventas', 'tipo' => 'ingreso', 'color' => '#10b981', 'sistema' => false],
        ['nombre' => 'Otros ingresos', 'tipo' => 'ingreso', 'color' => '#84cc16', 'sistema' => false],
    ];

    protected $fillable = ['user_id', 'nombre', 'tipo', 'color', 'sistema'];

    protected $casts = [
        'sistema' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }
}
