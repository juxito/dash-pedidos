<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PedidoSeeder extends Seeder
{
    use WithoutModelEvents;

    private const CLIENTES_TOTAL = 100;
    private const PEDIDOS_TOTAL = 1000;
    private const CHUNK_SIZE = 100;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->crearClientes();
        $this->crearPedidosConProductos();
    }

    /**
     * Inserta clientes de prueba en chunks.
     */
    private function crearClientes(): void
    {
        $clientes = [];

        for ($i = 0; $i < self::CLIENTES_TOTAL; $i++) {
            $clientes[] = [
                'nombre' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'telefono' => fake()->phoneNumber(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($clientes, self::CHUNK_SIZE) as $chunk) {
            DB::table('clientes')->insert($chunk);
        }
    }

    /**
     * Genera 1000 pedidos con productos pivote usando inserts masivos, sin bucles N+1.
     */
    private function crearPedidosConProductos(): void
    {
        $now = now();
        $pedidos = [];
        $pivotes = [];
        $estadosDisponibles = ['pendiente', 'entregado', 'cancelado'];

        for ($id = 1; $id <= self::PEDIDOS_TOTAL; $id++) {
            $diasOffset = fake()->numberBetween(-15, 15);
            $fechaEntrega = (clone $now)->addDays($diasOffset)->format('Y-m-d');

            $random = fake()->numberBetween(1, 100);
            $estado = match (true) {
                $random <= 40  => 'entregado',
                $random <= 50  => 'cancelado',
                default        => 'pendiente',
            };

            $numProductos = fake()->numberBetween(1, 5);
            $productosUsados = [];
            $totalPedido = 0;

            for ($j = 0; $j < $numProductos; $j++) {
                $productoId = fake()->numberBetween(1, 20);

                if (in_array($productoId, $productosUsados, true)) {
                    continue;
                }

                $productosUsados[] = $productoId;
                $cantidad = fake()->numberBetween(1, 5);
                $precioUnitario = fake()->randomFloat(2, 5, 300);

                $pivotes[] = [
                    'pedido_id'       => $id,
                    'producto_id'     => $productoId,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $precioUnitario,
                ];

                $totalPedido += $cantidad * $precioUnitario;
            }

            $pedidos[] = [
                'id'            => $id,
                'cliente_id'    => fake()->numberBetween(1, self::CLIENTES_TOTAL),
                'fecha_entrega' => $fechaEntrega,
                'total'         => round($totalPedido, 2),
                'estado'        => $estado,
                'created_at'    => (clone $now)->subDays(fake()->numberBetween(1, 30)),
                'updated_at'    => $now,
            ];
        }

        foreach (array_chunk($pedidos, self::CHUNK_SIZE) as $chunk) {
            DB::table('pedidos')->insert($chunk);
        }

        foreach (array_chunk($pivotes, self::CHUNK_SIZE) as $chunk) {
            DB::table('pedido_producto')->insert($chunk);
        }
    }
}