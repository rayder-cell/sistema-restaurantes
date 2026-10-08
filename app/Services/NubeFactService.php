<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NubeFactService
{
    protected string $ruta;
    protected string $token;

    public function __construct()
    {
        $this->ruta  = config('nubefact.ruta');
        $this->token = config('nubefact.token');
    }

    public function emitirBoleta(array $datos, int $tipoComprobante = 2): array
    {
        $items = [];
        $totalGravada = 0;
        $totalIgv = 0;

        foreach ($datos['items'] as $item) {
            $valorUnitario = round($item['valor_unitario'], 2);
            $precioUnitario = round($item['precio_unitario'], 2);
            $cantidad = $item['cantidad'];

            $subtotalLinea = round($valorUnitario * $cantidad, 2);
            $igvLinea = round(($precioUnitario - $valorUnitario) * $cantidad, 2);
            $totalLinea = round($precioUnitario * $cantidad, 2);

            $totalGravada += $subtotalLinea;
            $totalIgv += $igvLinea;

            $items[] = [
                'unidad_de_medida'   => 'NIU',
                'codigo'             => $item['codigo'] ?? '001',
                'descripcion'        => $item['descripcion'],
                'cantidad'           => $cantidad,
                'valor_unitario'     => $valorUnitario,
                'precio_unitario'    => $precioUnitario,
                'subtotal'           => $subtotalLinea,
                'tipo_de_igv'        => 1,
                'igv'                => $igvLinea,
                'total'              => $totalLinea,
                'anticipo_regularizacion' => false,
            ];
        }

        $total = round($totalGravada + $totalIgv, 2);

        $payload = [
            'operacion'                 => 'generar_comprobante',
            'tipo_de_comprobante'       => $tipoComprobante, // 1 = Factura, 2 = Boleta
            'serie'                     => $datos['serie'],
            'numero'                    => $datos['numero'],
            'sunat_transaction'         => 1,
            'cliente_tipo_de_documento' => $datos['cliente_tipo_documento'] ?? 1,
            'cliente_numero_de_documento' => $datos['cliente_numero_documento'] ?? '00000000',
            'cliente_denominacion'      => $datos['cliente_denominacion'] ?? 'Cliente Varios',
            'cliente_direccion'         => $datos['cliente_direccion'] ?? '',
            'cliente_email'             => $datos['cliente_email'] ?? '',
            'fecha_de_emision'          => now()->format('d-m-Y'),
            'moneda'                    => 1,
            'porcentaje_de_igv'         => 18.00,
            'total_gravada'             => $totalGravada,
            'total_igv'                 => $totalIgv,
            'total'                     => $total,
            'observaciones'             => $datos['observaciones'] ?? '',
            'items'                     => $items,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token token="' . $this->token . '"',
                'Content-Type'  => 'application/json',
            ])->timeout(20)->post($this->ruta, $payload);

            $body = $response->json();

            // Éxito = el documento fue generado (tiene enlace de PDF/XML y no hay errores),
            // independientemente de si SUNAT ya lo validó (eso puede tardar y se consulta aparte).
            $seGenero = $response->successful()
                && !empty($body['enlace_del_pdf'])
                && empty($body['errors']);

            if ($seGenero) {
                return [
                    'success' => true,
                    'message' => 'Comprobante generado correctamente.',
                    'data'    => $body,
                ];
            }

            Log::warning('NubeFacT: comprobante no generado', ['response' => $body]);

            return [
                'success' => false,
                'message' => $body['errors'] ?? 'NubeFacT no pudo generar el comprobante.',
                'data'    => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('NubeFacT: error de conexión', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'No se pudo conectar con NubeFacT.',
                'data'    => [],
            ];
        }
    }

    /**
     * Consulta el estado de validación SUNAT de un comprobante ya emitido.
     * Útil para revisar más tarde si aceptada_por_sunat pasó a true.
     */
    public function consultarEstado(int $tipoComprobante, string $serie, int $numero): array
    {
        $payload = [
            'operacion' => 'consultar_comprobante',
            'tipo_de_comprobante' => $tipoComprobante,
            'serie' => $serie,
            'numero' => $numero,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Token token="' . $this->token . '"',
            'Content-Type'  => 'application/json',
        ])->timeout(20)->post($this->ruta, $payload);

        return $response->json() ?? [];
    }
}