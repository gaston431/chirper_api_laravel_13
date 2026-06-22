<?php

namespace App\Http\Services;

use GuzzleHttp\Client;

class WeatherService
{
    private string $apiEndpoint = 'https://api.open-meteo.com/v1/forecast';
    private Client $client;

    public function __construct() {
        $this->client = new Client();
    }

    public function getWeather(array $request): array
    {
        try {
            $response = $this->client->get($this->apiEndpoint, [
                'query' => [
                    'latitude' => $request['latitude'],
                    'longitude' => $request['longitude'],
                    // 'temperature_unit' => 'fahrenheit',
                    'current' => 'temperature_2m,wind_speed_10m,relative_humidity_2m,precipitation_probability'
                ]
            ]);
            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            // Cualquier otro error inesperado (ej. problemas al decodificar el JSON)
            // throw new \Exception("Error de cURL: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Ocurrió un error inesperado al procesar el clima.',
                'details' => $e->getMessage()
            ];
        }
    }

    public function getWeatherCurl(array $request): array
    {
        $fields = [
            'latitude' => $request['latitude'],
            'longitude' => $request['longitude'],
            'current' => 'temperature_2m,wind_speed_10m,relative_humidity_2m,precipitation_probability'
        ];
        $fields_string = http_build_query($fields);

        try {
            $curl= curl_init($this->apiEndpoint . '?' . $fields_string);
            curl_setopt($curl,CURLOPT_FOLLOWLOCATION,1);
            curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
            $data = curl_exec($curl);
            // curl_close($curl);
            
            return json_decode($data,true);
        } catch (\Exception $e) {
            // Este bloque atrapa cualquier error lanzado arriba
            // echo "Se produjo un error en la petición: " . $e->getMessage();
            // throw new \Exception("Error de cURL: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Ocurrió un error inesperado al procesar el clima.',
                'details' => $e->getMessage()
            ];
        }

    }
}