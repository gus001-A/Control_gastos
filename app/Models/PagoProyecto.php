<?php

namespace App\Models;

use App\Models\Concerns\SerializaFechaCorta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoProyecto extends Model
{
    use HasFactory, SerializaFechaCorta;

    protected $table = 'pagos_proyecto';

    protected $fillable = ['proyecto_id', 'cuenta_id', 'movimiento_id', 'monto', 'fecha', 'notas'];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'date:Y-m-d',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(Movimiento::class);
    }
}
