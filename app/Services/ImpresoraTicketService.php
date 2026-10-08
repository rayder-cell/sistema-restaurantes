<?php

namespace App\Services;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class ImpresoraTicketService
{
    protected string $nombreImpresora;

    public function __construct()
    {
        $this->nombreImpresora = config('impresora.nombre', 'SATQ23T');
    }

    public function estaDisponible(): bool
    {
        try {
            $connector = new WindowsPrintConnector($this->nombreImpresora);
            $printer = new Printer($connector);
            $printer->close();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Imprime un ticket con estructura equivalente al PDF del comprobante.
     *
     * $datos:
     *   'restaurante', 'direccion', 'ruc', 'tipo' (boleta|factura), 'numero_completo',
     *   'fecha', 'mesa' (opcional), 'cliente_nombre', 'cliente_documento',
     *   'items' => [['cantidad','descripcion','precio_unitario','total']],
     *   'subtotal', 'igv', 'total', 'monto_pagado', 'vuelto', 'metodo_pago',
     *   'etiqueta' (opcional, ej. 'Copia Cliente' / 'Copia Restaurante')
     */
    public function imprimirTicket(array $datos): array
    {
        if (!$this->estaDisponible()) {
            return [
                'success' => false,
                'message' => 'La impresora no está conectada o encendida.',
            ];
        }

        try {
            $connector = new WindowsPrintConnector($this->nombreImpresora);
            $printer = new Printer($connector);

            $anchoLinea = 32; // caracteres por línea en fuente normal 80mm

            // ── Etiqueta de copia (opcional) ──
            if (!empty($datos['etiqueta'])) {
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->setEmphasis(true);
                $printer->text('*** ' . mb_strtoupper($datos['etiqueta']) . ' ***' . "\n");
                $printer->setEmphasis(false);
                $printer->feed(1);
            }

            // ── Encabezado ──
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text(mb_strtoupper($datos['restaurante']) . "\n");
            $printer->setEmphasis(false);

            if (!empty($datos['direccion'])) {
                $printer->text($datos['direccion'] . "\n");
            }

            $printer->text("RUC: " . $datos['ruc'] . "\n");

            $tituloComprobante = $datos['tipo'] === 'factura'
                ? 'FACTURA DE VENTA ELECTRONICA'
                : 'BOLETA DE VENTA ELECTRONICA';

            $printer->setEmphasis(true);
            $printer->text($tituloComprobante . "\n");
            $printer->setEmphasis(false);

            if (!empty($datos['metodo_pago'])) {
                $printer->text("F. PAGO: " . mb_strtoupper($datos['metodo_pago']) . "\n");
            }

            $printer->text($datos['numero_completo'] . "\n");
            $printer->text($datos['fecha'] . "\n");
            $printer->text(str_repeat('-', $anchoLinea) . "\n");

            // ── Cliente ──
            if (!empty($datos['cliente_documento'])) {
                $printer->text("RUC/DNI: " . $datos['cliente_documento'] . "\n");
            }
            if (!empty($datos['cliente_nombre'])) {
                $printer->text($datos['cliente_nombre'] . "\n");
            }
            if (!empty($datos['mesa'])) {
                $printer->text("Mesa: " . $datos['mesa'] . "\n");
            }
            $printer->text(str_repeat('-', $anchoLinea) . "\n");

            // ── Ítems (columnas bien separadas, como boleta de referencia) ──
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text(sprintf("%-8s%12s%12s\n", 'CANT.', 'P. UNIT.', 'P. TOT.'));

            foreach ($datos['items'] as $item) {
                $printer->text($item['descripcion'] . "\n");
                $printer->text(sprintf(
                    "%-8s%12s%12s\n",
                    $item['cantidad'] . ' UND',
                    'S/ ' . number_format($item['precio_unitario'], 2),
                    'S/ ' . number_format($item['total'], 2)
                ));
            }
            $printer->text(str_repeat('-', $anchoLinea) . "\n");

            // ── Totales: bloque desplazado hacia la derecha del papel ──
            $printer->setJustification(Printer::JUSTIFY_LEFT);

            $margenIzquierdo = 22; // espacios antes de la etiqueta, para "empujar" el bloque a la derecha
            $anchoEtiqueta = 12;   // ancho fijo reservado para la etiqueta (Subtotal, IGV, Total, Pago, Vuelto)

            $lineaTotal = function (string $etiqueta, float $monto) use ($margenIzquierdo, $anchoEtiqueta) {
                $valor = 'S/ ' . number_format($monto, 2);
                return str_repeat(' ', $margenIzquierdo)
                    . sprintf('%-' . $anchoEtiqueta . 's', $etiqueta)
                    . $valor . "\n";
            };

            $printer->text($lineaTotal('Subtotal',   $datos['subtotal']));
            $printer->text($lineaTotal('IGV (18%)',  $datos['igv']));
            $printer->setEmphasis(true);
            $printer->text($lineaTotal('Total',      $datos['total']));
            $printer->setEmphasis(false);

            $montoPagado = $datos['monto_pagado'] ?? $datos['total'];
            $vuelto = $datos['vuelto'] ?? 0;
            $printer->text($lineaTotal('Pago',       $montoPagado));
            $printer->text($lineaTotal('Vuelto',     $vuelto));

            $printer->text(str_repeat('-', $anchoLinea) . "\n");

            // ── SON: monto en letras ──
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("SON: " . $this->montoEnLetras($datos['total']) . "\n");
            $printer->text(str_repeat('-', $anchoLinea) . "\n");

            if (!empty($datos['metodo_pago'])) {
                $printer->text(mb_strtoupper($datos['metodo_pago']) . " S/ " . number_format($montoPagado, 2) . "\n");
                $printer->text(str_repeat('-', $anchoLinea) . "\n");
            }

            $printer->feed(1);
            $printer->text("Gracias por su visita\n");

            $printer->feed(3);
            $printer->cut();
            $printer->close();

            return ['success' => true, 'message' => 'Ticket impreso correctamente.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error al imprimir: ' . $e->getMessage()];
        }
    }

    /**
     * Imprime dos copias del mismo comprobante: una para el cliente
     * y otra para el archivo interno del restaurante.
     */
    public function imprimirTicketDoble(array $datos): array
    {
        $resultadoUno = $this->imprimirTicket($datos);

        if (!$resultadoUno['success']) {
            return $resultadoUno;
        }

        $resultadoDos = $this->imprimirTicket($datos);

        if (!$resultadoDos['success']) {
            return [
                'success' => false,
                'message' => 'Se imprimió la primera copia, pero falló la segunda: ' . $resultadoDos['message'],
            ];
        }

        return ['success' => true, 'message' => 'Ambas copias del ticket se imprimieron correctamente.'];
    }

    /**
     * Convierte un monto en soles a su representación en letras, ej:
     * 54.98 -> "CINCUENTA Y CUATRO Y 98/100 SOLES"
     */
    protected function montoEnLetras(float $monto): string
    {
        $entero = (int) floor($monto);
        $centavos = (int) round(($monto - $entero) * 100);

        $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $especiales = [
            10 => 'DIEZ',
            11 => 'ONCE',
            12 => 'DOCE',
            13 => 'TRECE',
            14 => 'CATORCE',
            15 => 'QUINCE',
            16 => 'DIECISEIS',
            17 => 'DIECISIETE',
            18 => 'DIECIOCHO',
            19 => 'DIECINUEVE',
            20 => 'VEINTE',
        ];
        $decenas = ['', '', '', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        $convertir = function (int $n) use (&$convertir, $unidades, $especiales, $decenas, $centenas) {
            if ($n === 0) return 'CERO';
            if ($n === 100) return 'CIEN';
            if ($n < 10) return $unidades[$n];
            if ($n <= 20) return $especiales[$n];
            if ($n < 30) {
                $u = $n % 10;
                return $u === 0 ? 'VEINTE' : 'VEINTI' . $unidades[$u];
            }
            if ($n < 100) {
                $d = intdiv($n, 10);
                $u = $n % 10;
                return $decenas[$d] . ($u > 0 ? ' Y ' . $unidades[$u] : '');
            }
            if ($n < 1000) {
                $c = intdiv($n, 100);
                $resto = $n % 100;
                return trim($centenas[$c] . ($resto > 0 ? ' ' . $convertir($resto) : ''));
            }
            if ($n < 1000000) {
                $miles = intdiv($n, 1000);
                $resto = $n % 1000;
                $prefijoMil = $miles === 1 ? 'MIL' : $convertir($miles) . ' MIL';
                return trim($prefijoMil . ($resto > 0 ? ' ' . $convertir($resto) : ''));
            }
            return (string) $n;
        };

        $texto = $entero === 0 ? 'CERO' : $convertir($entero);

        return $texto . ' Y ' . str_pad((string) $centavos, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
    }
}
