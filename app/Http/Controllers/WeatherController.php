<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\WeatherService;

class WeatherController extends Controller
{

    public function __construct(private WeatherService $weatherService) {}

    public function getWeather(Request $request){
        $validated = $request->validate([
            'latitude' => 'required|decimal:0,4',
            'longitude' => 'required|decimal:0,4',
        ]);

        $data = $this->weatherService->getWeather($validated);

        return [
            "temperature_2m" => $data['current']['temperature_2m'],
            "wind_speed_10m" => $data['current']['wind_speed_10m'],
            "relative_humidity_2m" => $data['current']['relative_humidity_2m'],
            "precipitation_probability" => $data['current']['precipitation_probability'],
        ];
    }

    public function getWeatherCurl(Request $request){
        $validated = $request->validate([
            'latitude' => 'required|decimal:0,4',
            'longitude' => 'required|decimal:0,4',
        ]);

        $data = $this->weatherService->getWeatherCurl($validated);

        return [
            "temperature_2m" => $data['current']['temperature_2m'],
            "wind_speed_10m" => $data['current']['wind_speed_10m'],
            "relative_humidity_2m" => $data['current']['relative_humidity_2m'],
            "precipitation_probability" => $data['current']['precipitation_probability'],
        ];
    }
}
