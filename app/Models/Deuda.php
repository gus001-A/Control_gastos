<?php

namespace App\Models;

use App\Models\Concerns\SerializaFechaCorta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deuda extends Model
{
    use HasFactory, SerializaFechaCorta;

    protected $fillable = [
        'user_id', 'nombre', 'acreedor', 'monto_total',
        'fecha_inicio', 'fecha_limite', 'estado', 'notas',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'fecha_inicio' => 'date:Y-m-d',
        'fecha_limite' => 'date:Y-m-d',
    ];

    protected $appends = ['pagado', 'aumentos', 'monto_total_efectivo', 'restante', 'progreso'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Todos los movimientos de la deuda: abonos y aumentos, más recientes primero. */
    public function movimientosDeuda(): HasMany
    {
        return $this->hasMany(AbonoDeuda::class)->orderByDesc('fecha')->orderByDesc('id');
    }

    public function abonos(): HasMany
    {
        return $this->hasMany(AbonoDeuda::class)->where('tipo', 'abono');
    }

    public function aumentosDeuda(): HasMany
    {
        return $this->hasMany(AbonoDeuda::class)->where('tipo', 'aumento');
    }

    public function getPagadoAttribute(): float
    {
        if (array_key_exists('abonos_sum_monto', $this->attributes)) {
            return round((float) ($this->attributes['abonos_sum_monto'] ?? 0), 2);
        }

        return round((float) $this->abonos()->sum('monto'), 2);
    }

    public function getAumentosAttribute(): float
    {
        if (array_key_exists('aumentos_deuda_sum_monto', $this->attributes)) {
            return round((float) ($this->attributes['aumentos_deuda_sum_monto'] ?? 0), 2);
        }

        return round((float) $this->aumentosDeuda()->sum('monto'), 2);
    }

    public function getMontoTotalEfectivoAttribute(): float
    {
        return round((float) $this->monto_total + $this->aumentos, 2);
    }

    public function getRestanteAttribute(): float
    {
        return round($this->montoTotalEfectivo - $this->pagado, 2);
    }

    public function getProgresoAttribute(): int
    {
        $total = $this->montoTotalEfectivo;
        if ($total <= 0) {
            return 0;
        }

        return (int) min(100, round($this->pagado / $total * 100));
    }

    public function revisarEstado(): void
    {
        $nuevoEstado = $this->restante <= 0 ? 'pagada' : 'activa';
        if ($nuevoEstado !== $this->estado) {
            $this->update(['estado' => $nuevoEstado]);
        }
    }
}
