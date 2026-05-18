<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    use WithoutModelEvents;

    private const CHUNK_SIZE = 50;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = [
            ['nombre' => 'Manejo Especial', 'sku' => 'MAN-ESP-001', 'precio' => 150.00],
            ['nombre' => 'Camiseta Básica', 'sku' => 'CAM-BAS-001', 'precio' => 29.99],
            ['nombre' => 'Pantalón Vaquero', 'sku' => 'PAN-VAQ-001', 'precio' => 59.99],
            ['nombre' => 'Zapatos Deportivos', 'sku' => 'ZAP-DEP-001', 'precio' => 89.99],
            ['nombre' => 'Chaqueta Impermeable', 'sku' => 'CHQ-IMP-001', 'precio' => 120.00],
            ['nombre' => 'Mochila Viajera', 'sku' => 'MOC-VIA-001', 'precio' => 45.50],
            ['nombre' => 'Auriculares Bluetooth', 'sku' => 'AUR-BLU-001', 'precio' => 79.99],
            ['nombre' => 'Cargador Rápido', 'sku' => 'CAR-RAP-001', 'precio' => 25.00],
            ['nombre' => 'Funda Móvil Silicona', 'sku' => 'FUN-MOV-001', 'precio' => 12.99],
            ['nombre' => 'Libreta A5', 'sku' => 'LIB-A5-001', 'precio' => 8.50],
            ['nombre' => 'Bolígrafo Metálico', 'sku' => 'BOL-MET-001', 'precio' => 5.99],
            ['nombre' => 'Taza Cerámica', 'sku' => 'TAZ-CER-001', 'precio' => 14.99],
            ['nombre' => 'Lámpara LED Escritorio', 'sku' => 'LAM-LED-001', 'precio' => 34.99],
            ['nombre' => 'Teclado Mecánico', 'sku' => 'TEC-MEC-001', 'precio' => 89.00],
            ['nombre' => 'Ratón Inalámbrico', 'sku' => 'RAT-INA-001', 'precio' => 39.99],
            ['nombre' => 'Monitor 24 Pulgadas', 'sku' => 'MON-24-001', 'precio' => 199.99],
            ['nombre' => 'Webcam HD', 'sku' => 'WEB-HD-001', 'precio' => 55.00],
            ['nombre' => 'Altavoz Portátil', 'sku' => 'ALT-POR-001', 'precio' => 49.99],
            ['nombre' => 'Silla Ergonómica', 'sku' => 'SIL-ERG-001', 'precio' => 299.99],
            ['nombre' => 'Mesa Plegable', 'sku' => 'MES-PLE-001', 'precio' => 89.99],
        ];

        $chunks = array_chunk($productos, self::CHUNK_SIZE);

        foreach ($chunks as $chunk) {
            DB::table('productos')->insert($chunk);
        }
    }
}