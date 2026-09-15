<?php

namespace App\Models;

use App\Models\Concerns\SerializaFechaCorta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cuenta extends Model
{
    use HasFactory, SerializaFechaCorta;

    public const TIPOS = [
        'efectivo' => 'Efectivo',
        'banco' => 'Cuenta bancaria',
        'ahorro' => 'Cuenta de ahorro',
        'tarjeta_credito' => 'Tarjeta de crédito',
        'tarjeta_debito' => 'Tarjeta de débito',
        'inversion' => 'Inversión',
        'otro' => 'Otro',
    ];

    protected $fillable = [
        'user_id', 'nombre', 'tipo', 'saldo_inicial', 'color', 'activa', 'notas',
    ];

    protected $casts = [
        'saldo_inicial' => 'decimal:2',
        'activa' => 'boolean',
    ];

    protected $appends = ['saldo'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    public function transferenciasSalida(): HasMany
    {
        return $this->hasMany(Transferencia::class, 'cuenta_origen_id');
    }

    public function transferenciasEntrada(): HasMany
    {
        return $this->hasMany(Transferencia::class, 'cuenta_destino_id');
    }

    /**
     * Trae el saldo ya calculado en una sola consulta (evita N+1 al listar varias cuentas).
     * Úsalo con Cuenta::conSaldo()->get() en vez de Cuenta::all()/get().
     */
    public function scopeConSaldo($query)
    {
        return $query->select('cuentas.*')->selectSub(
            'cuentas.saldo_inicial'
            .' + COALESCE((SELECT SUM(monto) FROM movimientos WHERE movimientos.cuenta_id = cuentas.id AND movimientos.tipo = \'ingreso\'), 0)'
            .' - COALESCE((SELECT SUM(monto) FROM movimientos WHERE movimientos.cuenta_id = cuentas.id AND movimientos.tipo = \'gasto\'), 0)'
            .' - COALESCE((SELECT SUM(monto) FROM transferencias WHERE transferencias.cuenta_origen_id = cuentas.id), 0)'
            .' + COALESCE((SELECT SUM(monto) FROM transferencias WHERE transferencias.cuenta_destino_id = cuentas.id), 0)',
            'saldo_calculado'
        );
    }

    public function getSaldoAttribute(): float
    {
        if (array_key_exists('saldo_calculado', $this->attributes)) {
            return round((float) $this->attributes['saldo_calculado'], 2);
        }

        $saldo = (float) $this->saldo_inicial;
        $saldo += (float) $this->movimientos()->where('tipo', 'ingreso')->sum('monto');
        $saldo -= (float) $this->movimientos()->where('tipo', 'gasto')->sum('monto');
        $saldo -= (float) $this->transferenciasSalida()->sum('monto');
        $saldo += (float) $this->transferenciasEntrada()->sum('monto');

        return round($saldo, 2);
    }

    public function tieneMovimientos(): bool
    {
        return $this->movimientos()->exists()
            || $this->transferenciasSalida()->exists()
            || $this->transferenciasEntrada()->exists();
    }

    /**
     * Saldo disponible ignorando el efecto de un movimiento propio (para validar al editarlo).
     */
    public function saldoSinMovimiento(?Movimiento $movimiento): float
    {
        $saldo = $this->saldo;

        if ($movimiento && (int) $movimiento->cuenta_id === (int) $this->id) {
            $saldo += $movimiento->tipo === 'ingreso' ? -(float) $movimiento->monto : (float) $movimiento->monto;
        }

        return round($saldo, 2);
    }
}
