<?php

namespace App\Models;

use App\Models\Concerns\SerializaFechaCorta;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CargoRecurrente extends Model
{
    use HasFactory, SerializaFechaCorta;

    protected $table = 'cargos_recurrentes';

    public const FRECUENCIAS = [
        'semanal' => 'Semanal',
        'quincenal' => 'Quincenal',
        'mensual' => 'Mensual',
        'bimestral' => 'Cada 2 meses',
        'trimestral' => 'Cada 3 meses',
        'semestral' => 'Cada 6 meses',
        'anual' => 'Anual',
    ];

    protected $fillable = [
        'user_id', 'cuenta_id', 'categoria_id', 'nombre', 'monto', 'frecuencia',
        'proxima_fecha', 'fecha_fin', 'activo', 'notas',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'proxima_fecha' => 'date:Y-m-d',
        'fecha_fin' => 'date:Y-m-d',
        'activo' => 'boolean',
    ];

    protected $appends = ['dias_para_vencer', 'vencido'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    public function getDiasParaVencerAttribute(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->proxima_fecha, false);
    }

    public function getVencidoAttribute(): bool
    {
        return $this->proxima_fecha->lt(now()->startOfDay());
    }

    /**
     * Calcula la siguiente fecha de cobro a partir de una fecha base, según la frecuencia.
     */
    public function siguienteFechaDesde(Carbon $desde): Carbon
    {
        return match ($this->frecuencia) {
            'semanal' => $desde->copy()->addWeek(),
            'quincenal' => $desde->copy()->addDays(15),
            'mensual' => $desde->copy()->addMonthNoOverflow(),
            'bimestral' => $desde->copy()->addMonthsNoOverflow(2),
            'trimestral' => $desde->copy()->addMonthsNoOverflow(3),
            'semestral' => $desde->copy()->addMonthsNoOverflow(6),
            'anual' => $desde->copy()->addYearNoOverflow(),
            default => $desde->copy()->addMonthNoOverflow(),
        };
    }
}
