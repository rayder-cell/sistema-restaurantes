<?php

namespace App\Helpers;

class NumeroALetras
{
    private static array $unidades = [
        '', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve',
        'diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete',
        'dieciocho', 'diecinueve', 'veinte',
    ];

    private static array $decenas = [
        2 => 'veinti', 3 => 'treinta', 4 => 'cuarenta', 5 => 'cincuenta',
        6 => 'sesenta', 7 => 'setenta', 8 => 'ochenta', 9 => 'noventa',
    ];

    private static array $centenas = [
        1 => 'ciento', 2 => 'doscientos', 3 => 'trescientos', 4 => 'cuatrocientos',
        5 => 'quinientos', 6 => 'seiscientos', 7 => 'setecientos', 8 => 'ochocientos', 9 => 'novecientos',
    ];

    public static function convertir(float $numero): string
    {
        $entero = (int) floor($numero);
        $decimales = (int) round(($numero - $entero) * 100);

        $texto = self::convertirEntero($entero);
        $texto .= ' y ' . str_pad((string) $decimales, 2, '0', STR_PAD_LEFT) . '/100';

        return $texto;
    }

    private static function convertirEntero(int $n): string
    {
        if ($n === 0) return 'cero';
        if ($n === 100) return 'cien';

        if ($n < 21) {
            return self::$unidades[$n];
        }

        if ($n < 30) {
            $resto = $n - 20;
            return $resto === 0 ? 'veinte' : 'veinti' . self::$unidades[$resto];
        }

        if ($n < 100) {
            $dec = intdiv($n, 10);
            $resto = $n % 10;
            $texto = self::$decenas[$dec];
            return $resto === 0 ? $texto : $texto . ' y ' . self::$unidades[$resto];
        }

        if ($n < 1000) {
            $cen = intdiv($n, 100);
            $resto = $n % 100;
            $texto = self::$centenas[$cen];
            return $resto === 0 ? $texto : $texto . ' ' . self::convertirEntero($resto);
        }

        if ($n < 1000000) {
            $miles = intdiv($n, 1000);
            $resto = $n % 1000;
            $prefijo = $miles === 1 ? 'mil' : self::convertirEntero($miles) . ' mil';
            return $resto === 0 ? $prefijo : $prefijo . ' ' . self::convertirEntero($resto);
        }

        $millones = intdiv($n, 1000000);
        $resto = $n % 1000000;
        $prefijo = $millones === 1 ? 'un millón' : self::convertirEntero($millones) . ' millones';
        return $resto === 0 ? $prefijo : $prefijo . ' ' . self::convertirEntero($resto);
    }
}