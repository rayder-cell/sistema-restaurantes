<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CulqiService
{
    protected string $secretKey;
    protected string $baseUrl = 'https://api.culqi.com/v2';

    public function __construct()
    {
        $this->secretKey = config('culqi.secret_key');
    }

    /**
     * Crea un cargo en Culqi a partir de un token generado en el frontend
     * (Culqi Checkout.js) para pago con tarjeta.
     *
     * @param string $token       Token generado por Culqi.js (tkn_...)
     * @param float  $monto       Monto en soles (ej: 45.50)
     * @param string $email       Email del cliente (Culqi lo exige)
     * @param string $descripcion Descripción del cargo
     * @return array{success: bool, charge_id: ?string, message: string, raw: array}
     */
    public function crearCargo(string $token, float $monto, string $email, string $descripcion = 'Consumo restaurante'): array
    {
        $montoCentimos = (int) round($monto * 100);

        try {
            $response = Http::withToken($this->secretKey)
                ->timeout(15)
                ->post("{$this->baseUrl}/charges", [
                    'amount'        => $montoCentimos,
                    'currency_code' => 'PEN',
                    'email'         => $email,
                    'source_id'     => $token,
                    'description'   => $descripcion,
                ]);

            $body = $response->json();

            if ($response->successful() && isset($body['id']) && ($body['outcome']['type'] ?? null) === 'venta_exitosa') {
                return [
                    'success'   => true,
                    'charge_id' => $body['id'],
                    'message'   => 'Cargo aprobado.',
                    'raw'       => $body,
                ];
            }

            $mensaje = $body['user_message'] ?? $body['merchant_message'] ?? 'El cargo fue rechazado por Culqi.';

            Log::warning('Culqi: cargo rechazado', ['response' => $body]);

            return [
                'success'   => false,
                'charge_id' => null,
                'message'   => $mensaje,
                'raw'       => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('Culqi: error al crear cargo', ['error' => $e->getMessage()]);

            return [
                'success'   => false,
                'charge_id' => null,
                'message'   => 'No se pudo conectar con la pasarela de pago. Intenta nuevamente.',
                'raw'       => [],
            ];
        }
    }
}