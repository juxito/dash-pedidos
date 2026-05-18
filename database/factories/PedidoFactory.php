<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pedido>
 */
class PedidoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'fecha_entrega' => fake()->dateTimeBetween('-10 days', '+10 days')->format('Y-m-d'),
            'total' => fake()->randomFloat(2, 50, 2000),
            'estado' => fake()->randomElement(['pendiente', 'entregado', 'cancelado']),
        ];
    }
}