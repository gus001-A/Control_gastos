<?php

namespace App\Models;

use App\Models\Concerns\SerializaFechaCorta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proyecto extends Model
{
    use HasFactory, SerializaFechaCorta;

    public const ESTADOS = [
        'activo' => 'Activo',
        'terminado' => 'Terminado',
        'cancelado' => 'Cancelado',
    ];

    protected $fillable = [
        'user_id', 'nombre', 'cliente', 'descripcion', 'monto_cobrado',
        'fecha_inicio', 'fecha_entrega', 'estado', 'notas',
    ];

    protected $casts = [
        'monto_cobrado' => 'decimal:2',
        'fecha_inicio' => 'date:Y-m-d',
        'fecha_entrega' => 'date:Y-m-d',
    ];

    protected $appends = ['pagado', 'pendiente', 'progreso'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoProyecto::class);
    }

    public function getPagadoAttribute(): float
    {
        if (array_key_exists('pagos_sum_monto', $this->attributes)) {
            return round((float) ($this->attributes['pagos_sum_monto'] ?? 0), 2);
        }

        return round((float) $this->pagos()->sum('monto'), 2);
    }

    public function getPendienteAttribute(): float
    {
        return round((float) $this->monto_cobrado - $this->pagado, 2);
    }

    public function getProgresoAttribute(): int
    {
        if ((float) $this->monto_cobrado <= 0) {
            return 0;
        }

        return (int) min(100, round($this->pagado / (float) $this->monto_cobrado * 100));
    }
}
