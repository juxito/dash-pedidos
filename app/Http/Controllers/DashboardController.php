<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Pedido;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Muestra los pedidos pendientes con entrega en los próximos 3 días.
     */
    public function porEnviar(): Response
    {
        $pedidos = Pedido::with(['cliente', 'productos'])
            ->porEnviar()
            ->orderBy('fecha_entrega')
            ->paginate(15);

        return Inertia::render('Dashboard', [
            'seccion' => 'por-enviar',
            'pedidos' => $pedidos,
        ]);
    }

    /**
     * Muestra los pedidos pendientes con fecha de entrega vencida.
     */
    public function retrasados(): Response
    {
        $pedidos = Pedido::with(['cliente', 'productos'])
            ->retrasados()
            ->orderBy('fecha_entrega')
            ->paginate(15);

        return Inertia::render('Dashboard', [
            'seccion' => 'retrasados',
            'pedidos' => $pedidos,
        ]);
    }

    /**
     * Muestra los pedidos entregados.
     */
    public function entregados(): Response
    {
        $pedidos = Pedido::with(['cliente', 'productos'])
            ->entregados()
            ->orderBy('fecha_entrega', 'desc')
            ->paginate(15);

        return Inertia::render('Dashboard', [
            'seccion' => 'entregados',
            'pedidos' => $pedidos,
        ]);
    }

    /**
     * Muestra los pedidos cancelados.
     */
    public function cancelados(): Response
    {
        $pedidos = Pedido::with(['cliente', 'productos'])
            ->cancelados()
            ->orderBy('fecha_entrega', 'desc')
            ->paginate(15);

        return Inertia::render('Dashboard', [
            'seccion' => 'cancelados',
            'pedidos' => $pedidos,
        ]);
    }
}