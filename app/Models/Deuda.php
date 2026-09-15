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

    protected $appends = ['pagado', 'restante', 'progreso'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function abonos(): HasMany
    {
        return $this->hasMany(AbonoDeuda::class);
    }

    public function getPagadoAttribute(): float
    {
        if (array_key_exists('abonos_sum_monto', $this->attributes)) {
            return round((float) ($this->attributes['abonos_sum_monto'] ?? 0), 2);
        }

        return round((float) $this->abonos()->sum('monto'), 2);
    }

    public function getRestanteAttribute(): float
    {
        return round((float) $this->monto_total - $this->pagado, 2);
    }

    public function getProgresoAttribute(): int
    {
        if ((float) $this->monto_total <= 0) {
            return 0;
        }

        return (int) min(100, round($this->pagado / (float) $this->monto_total * 100));
    }

    public function revisarEstado(): void
    {
        $nuevoEstado = $this->restante <= 0 ? 'pagada' : 'activa';
        if ($nuevoEstado !== $this->estado) {
            $this->update(['estado' => $nuevoEstado]);
        }
    }
}
