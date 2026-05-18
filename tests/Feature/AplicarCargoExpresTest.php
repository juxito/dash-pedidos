<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AplicarCargoExpresTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que el comando aplica el cargo del 10% correctamente.
     */
    public function test_cargo_expres_se_aplica_correctamente(): void
    {
        // Crear un cliente
        $cliente = Cliente::factory()->create();

        // Crear el producto especial (id=5)
        $productoEspecial = Producto::factory()->create([
            'id' => 5,
            'nombre' => 'Manejo Especial',
            'sku' => 'SPECIAL-001',
            'precio' => 50.00,
        ]);

        // Crear otro producto normal
        $productoNormal = Producto::factory()->create();

        // Crear un pedido pendiente con fecha de entrega mañana
        $tomorrow = now()->addDay();
        $pedidoParaActualizar = Pedido::factory()->create([
            'cliente_id' => $cliente->id,
            'fecha_entrega' => $tomorrow,
            'total' => 100.00,
            'estado' => 'pendiente',
        ]);

        // Vincular el producto especial al pedido
        $pedidoParaActualizar->productos()->attach($productoEspecial->id, [
            'cantidad' => 1,
            'precio_unitario' => 50.00,
        ]);

        // Crear un pedido pendiente pero sin el producto especial
        $pedidoNoActualizar = Pedido::factory()->create([
            'cliente_id' => $cliente->id,
            'fecha_entrega' => $tomorrow,
            'total' => 80.00,
            'estado' => 'pendiente',
        ]);
        $pedidoNoActualizar->productos()->attach($productoNormal->id, [
            'cantidad' => 1,
            'precio_unitario' => 80.00,
        ]);

        // Crear un pedido con producto especial pero con fecha diferente
        $pedidoFechaDistinta = Pedido::factory()->create([
            'cliente_id' => $cliente->id,
            'fecha_entrega' => now()->addDays(2),
            'total' => 120.00,
            'estado' => 'pendiente',
        ]);
        $pedidoFechaDistinta->productos()->attach($productoEspecial->id, [
            'cantidad' => 1,
            'precio_unitario' => 60.00,
        ]);

        // Ejecutar el comando
        $this->artisan('pedidos:cargo-expres')
            ->expectsOutput('✓ Cargo expres aplicado a 1 pedido(s)')
            ->assertExitCode(0);

        // Verificar que solo el primer pedido fue actualizado
        $this->assertEquals(110.00, $pedidoParaActualizar->refresh()->total);
        $this->assertEquals(80.00, $pedidoNoActualizar->refresh()->total);
        $this->assertEquals(120.00, $pedidoFechaDistinta->refresh()->total);
    }

    /**
     * Test que el comando no aplica cargo si no hay pedidos que cumplan con las condiciones.
     */
    public function test_comando_no_aplica_cargo_sin_coincidencias(): void
    {
        // Ejecutar el comando sin crear ningún pedido
        $this->artisan('pedidos:cargo-expres')
            ->expectsOutput('✓ Cargo expres aplicado a 0 pedido(s)')
            ->assertExitCode(0);
    }
}
