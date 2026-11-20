<?php

namespace Tests\Unit;

use App\Models\Nota;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NotaMediaTest extends TestCase
{
    public static function notasProvider(): array
    {
        return [
            'notas iguais' => [7, 7, 7.0],
            'bimestral com peso 2' => [6, 9, 8.0],
            'arredonda em 2 casas' => [8, 9, 8.67],
            'zeros' => [0, 0, 0.0],
            'máximas' => [10, 10, 10.0],
        ];
    }

    #[DataProvider('notasProvider')]
    public function test_calcula_media_ponderada(float $parcial, float $bimestral, float $esperado): void
    {
        $this->assertSame($esperado, Nota::calcularMedia($parcial, $bimestral));
    }
}
