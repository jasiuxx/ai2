<?php

namespace App\Tests\Entity;

use App\Entity\Measurement;
use PHPUnit\Framework\TestCase;

class MeasurementTest extends TestCase
{
    /**
     * @dataProvider dataGetFahrenheit
     */
    public function testGetFahrenheit($celsius, $expectedFahrenheit): void
    {
        $measurement = new Measurement();
        $measurement->setCelsius($celsius);
        $this->assertEquals((string)$expectedFahrenheit, $measurement->getFahrenheit());
    }

    public function dataGetFahrenheit(): array
    {
        return [
            ['0', 32],           // 0°C = 32°F
            ['-100', -148],      // -100°C = -148°F
            ['100', 212],        // 100°C = 212°F
            ['0.5', 32.9],       // 0.5°C = 32.9°F
            ['-10', 14],         // -10°C = 14°F
            ['25', 77],          // 25°C = 77°F
            ['37.5', 99.5],      // 37.5°C = 99.5°F
            ['50', 122],         // 50°C = 122°F
            ['-50.5', -58.9],    // -50.5°C = -59.9°F
            ['10.2', 50.4],      // 10.2°C = 50.4°F
        ];
    }
}
