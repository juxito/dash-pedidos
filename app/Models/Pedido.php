<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PedidoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pedido extends Model
{
    /** @use HasFactory<PedidoFactory> */
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'cliente_id',
        'fecha_entrega',
        'total',
        'estado',
    ];

    /**
     * Un pedido pertenece a un cliente.
     *
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Un pedido tiene muchos productos (tabla pivote).
     *
     * @return BelongsToMany<Producto, $this>
     */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'pedido_producto')
            ->withPivot('cantidad', 'precio_unitario');
    }

    /**
     * Scope: pedidos pendientes con fecha_entrega entre hoy y los próximos 3 días.
     *
     * @param  Builder<Pedido>  $query
     * @return Builder<Pedido>
     */
    public function scopePorEnviar(Builder $query): Builder
    {
        return $query
            ->where('estado', 'pendiente')
            ->whereBetween('fecha_entrega', [now()->startOfDay(), now()->addDays(3)->endOfDay()]);
    }

    /**
     * Scope: pedidos pendientes con fecha_entrega anterior a hoy.
     *
     * @param  Builder<Pedido>  $query
     * @return Builder<Pedido>
     */
    public function scopeRetrasados(Builder $query): Builder
    {
        return $query
            ->where('estado', 'pendiente')
            ->where('fecha_entrega', '<', now()->startOfDay());
    }

    /**
     * Scope: pedidos entregados.
     *
     * @param  Builder<Pedido>  $query
     * @return Builder<Pedido>
     */
    public function scopeEntregados(Builder $query): Builder
    {
        return $query->where('estado', 'entregado');
    }

    /**
     * Scope: pedidos cancelados.
     *
     * @param  Builder<Pedido>  $query
     * @return Builder<Pedido>
     */
    public function scopeCancelados(Builder $query): Builder
    {
        return $query->where('estado', 'cancelado');
    }
}