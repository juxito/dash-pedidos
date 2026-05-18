<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Pedido;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AplicarCargoExpres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pedidos:cargo-expres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aplica cargo de 10% a pedidos pendientes con fecha de entrega mañana que contienen producto especial (id=5)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Obtener la fecha de mañana sin hora
        $tomorrow = now()->addDay()->startOfDay();

        // Contar y actualizar pedidos en un único query masivo
        $updatedCount = Pedido::query()
            ->where('estado', 'pendiente')
            ->whereDate('fecha_entrega', $tomorrow->format('Y-m-d'))
            ->whereHas('productos', function ($q) {
                $q->where('productos.id', 5);
            })
            ->update(['total' => DB::raw('total * 1.10')]);

        // Registrar en log
        Log::info("Cargo expres aplicado: {$updatedCount} pedidos actualizados con incremento del 10%");

        $this->info("✓ Cargo expres aplicado a {$updatedCount} pedido(s)");

        return self::SUCCESS;
    }
}
