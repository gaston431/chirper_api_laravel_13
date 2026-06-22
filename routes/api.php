<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\WeatherController;
use App\Http\Resources\UserResource;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('posts', PostController::class);

Route::post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);
 
    return ['token' => $token->plainTextToken];
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
});

//Route::post('/posts',  [PostController::class, 'store'])->middleware('auth:sanctum');


Route::post('/login', function (Request $request) {
    // 1. Validar los datos de entrada
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // 2. Buscar al usuario explícitamente en la base de datos
    $user = User::where('email', $request->email)->first();

    // 3. Comprobar si el usuario existe y la contraseña coincide
    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Las credenciales proporcionadas son incorrectas.'],
        ]);
    }

    // 4. Ahora que $user no es null, generamos el token de forma segura
    $token = $user->createToken('auth_token', ['*'], now()->plus(weeks: 1))->plainTextToken;

    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
    ]);
});

Route::get('/users', function () {
    return User::with('posts')->paginate(10)->toResourceCollection();
    // return UserResource::collection(User::all());
});

Route::get('/getWeather', function (Request $request) {

    $latitude = $request->input('latitude');
    $longitude = $request->input('longitude');
    // var_dump($latitude,$longitude);die;
    // $apiKey = '7246de415ccc5d4ff9c4fbb2852575d6';
    // $apiEndpoint = 'https://api.openweathermap.org/data/2.5/weather';
    $apiEndpoint = 'https://api.open-meteo.com/v1/forecast';
    $client = new Client();

    $response = $client->get($apiEndpoint, [
        'query' => [
            'latitude' => $latitude,
            'longitude' => $longitude,
            // 'temperature_unit' => 'fahrenheit',
            'current' => 'temperature_2m,wind_speed_10m,relative_humidity_2m,precipitation_probability'
        ]
    ]);

    $data = json_decode($response->getBody()->getContents(), true);

    return [
        "temperature_2m" => $data['current']['temperature_2m'],
        "wind_speed_10m" => $data['current']['wind_speed_10m'],
        "relative_humidity_2m" => $data['current']['relative_humidity_2m'],
        "precipitation_probability" => $data['current']['precipitation_probability'],
    ];
    return [
        'city' => $data['name'],
        'temperature' => $data['main']['temp'],
        'description' => $data['weather'][0]['description'],
        'humidity' => $data['main']['humidity']
    ];
});

Route::get('getWeather', [WeatherController::class, 'getWeather']);
Route::get('getWeatherCurl', [WeatherController::class, 'getWeatherCurl']);
