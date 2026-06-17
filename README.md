<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).




en el metodo store como asociar el post a crear al user autenticado 11 de junio de 2026

Para asociar el post al usuario autenticado, debes utilizar el método $request->user() para obtener la instancia del usuario que envía la petición.A continuación, tienes las dos formas más eficientes de implementarlo en tu PostController:
Opción 1: A través de la relación de Eloquent (Recomendada)Esta es la forma más limpia y estándar en Laravel. Aprovecha la relación que existe entre el usuario y sus publicaciones.phppublic function store(Request $request)
{
    // 1. Validar los datos del post
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'body' => 'required|string',
    ]);

    // 2. Crear el post directamente desde la relación del usuario
    // Esto asigna automáticamente el 'user_id' correcto
    $post = $request->user()->posts()->create($validated);

    return response()->json($post, 201);
}
Usa el código con precaución.Nota: Para que esta opción funcione, debes tener definida la relación posts() dentro de tu modelo app/Models/User.php:phppublic function posts()
{
    return $this->hasMany(Post::class);
}
Usa el código con precaución.

Opción 2: Asignando el user_id manualmenteSi prefieres usar el método estático del modelo Post, puedes inyectar el identificador del usuario directamente en el array de datos validados.phppublic function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'body' => 'required|string',
    ]);

    // Añadir el id del usuario autenticado al array
    $validated['user_id'] = $request->user()->id;

    // Crear el registro en la base de datos
    $post = Post::create($validated);

    return response()->json($post, 201);
}
Usa el código con precaución.Nota: Recuerda incluir user_id dentro de la propiedad $fillable en tu modelo app/Models/Post.php si utilizas asignación masiva.⚠️ Requisito indispensablePara que $request->user() no devuelva null y provoque el mismo error de antes, tu ruta debe estar protegida por el middleware de Sanctum en tu archivo routes/api.php:phpRoute::middleware('auth:sanctum')->group(function () {
    Route::apiResource('posts', PostController::class);
});
Usa el código con precaución.Si estás probando esto en Postman, recuerda incluir la cabecera Authorization con el valor Bearer <tu_token_generado>.¿Te gustaría que configuremos las reglas de validación en un Form Request dedicado para limpiar el controlador, o prefieres ver cómo retornar la respuesta usando el PostResource que creamos al principio?Dijiste tengo este4 mensaje cuando hago this->authorize in funcion update en post controller "message": "Call to undefined method App\\Http\\Controllers\\PostController::authorize()",11 de junio de 2026A partir de las versiones modernas de Laravel (incluyendo Laravel 13), el controlador base fue simplificado al máximo y ya no incluye el método $this->authorize() por defecto.Tienes dos formas excelentes de solucionarlo: la forma moderna usando fachadas (recomendada para mantener tus controladores limpios) o la forma clásica si prefieres mantener tu código actual recuperando el rasgo (trait) ausente.Opción 1: Usar la fachada Gate (Enfoque Moderno de Laravel 13)No necesitas modificar la configuración del controlador base. Puedes invocar la autorización directamente importando la fachada Gate.phpuse App\Models\Post;
use Illuminate\Http\Request;
use Illuminateupport\Facades\Gate;

public function update(Request $request, Post $post)
{
    // Lanza automáticamente una excepción 403 (No autorizado) si falla la política
    Gate::authorize('update', $post);

    // Si pasa la validación, continúas con la lógica
    $validated = $request->validate([
        'title' => 'required|string|max:255',
    ]);

    $post->update($validated);

    return response()->json($post);
}
Usa el código con precaución.Opción 2: Recuperar $this->authorize() en el Controlador BaseSi estás siguiendo un tutorial o prefieres usar la sintaxis clásica de $this->authorize(), debes agregar manualmente el trait AuthorizesRequests que Laravel removió del esqueleto inicial.Abre tu controlador abstracto base en app/Http/Controllers/Controller.php.Agrega el uso del trait tal como se muestra a continuación:phpnamespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // 1. Importar el trait

abstract class Controller
{
    use AuthorizesRequests; // 2. Usar el trait aquí dentro
}
Usa el código con precaución.Al hacer este cambio, tu código actual $this->authorize('update', $post); volverá a funcionar instantáneamente en cualquier controlador que extienda de esta base.💡 Recordatorio sobre las Políticas (Policies)Para que cualquiera de las dos opciones funcione, asegúrate de tener creada la política correspondiente para tu modelo Post:bashphp artisan make:policy PostPolicy --model=Post
Usa el código con precaución.Y dentro de app/Policies/PostPolicy.php, define la regla que limita la actualización únicamente al propietario del registro:phpuse App\Models\Post;
use App\Models\User;

public function update(User $user, Post $post): bool
{
    // El usuario solo puede editar el post si es el creador del mismo
    return $user->id === $post->user_id;
}
Usa el código con precaución.¿Qué alternativa prefieres implementar para tu proyecto? Si lo deseas, podemos revisar cómo manejar de manera elegante el error 403 resultante en las respuestas JSON de tu API o configurar pruebas automatizadas (feature tests) para verificar que la restricción funcione correctamente.7 sitiosAuthorization | Laravel 13.x - The clean stack for Artisans and ...To authorize an action using gates, you should use the allows or denies methods provided by the Gate facade. Note that you are not...LaravelLaravel 11: New Slim Base Controller with No Traits - YouTubeLaravel 11: New Slim Base Controller with No Traits - YouTube. This content isn't available. Another new change in the Laravel 11 ...YouTubeHow to use resource policies [authorizeResource] in Laravel ...How to use resource policies [ authorizeResource ] in Laravel 11+ jalexmelendez. Follow. 2 min read. Mar 17, 2025. Laravel 11 intr...MediumLaravel 11's Controller Authorisation & Validation MethodsSecurity Tip: Laravel 11's Controller Authorisation & Validation Methods. Security Tip: Laravel 11's Controller Authorisation & Va...Securing LaravelMethod authorize() does not exists - Laravel 5.5 - Stack OverflowComments. ... In Laravel version 11 and up the reference to $this->authorize has been removed from the docs. You can still use tha...Stack OverflowMostrar todoDijiste tengo postResource que muestra el user, y userResource que muestra la collection de posts, esto genera Error:read ECONNRESET, como evitarlo, quiero que si ya se cargo uno no se cargue el otro11 de junio de 2026El error Error: read ECONNRESET ocurre en Postman (o tu cliente HTTP) porque has creado un bucle infinito de referencia circular.Al transformar los datos, el PostResource intenta renderizar el UserResource, el cual inmediatamente intenta renderizar la colección de PostResource de ese usuario, y así sucesivamente en una cadena interminable que agota la memoria del servidor o desborda la pila de PHP, obligando a Laragon/Apache/Nginx a cerrar abruptamente la conexión.Para evitarlo, debes usar el método condicional nativo de Laravel llamado $this->whenLoaded(). Esto asegura que las relaciones solo se incluyan si las llamaste explícitamente usando Eager Loading (with()) en tu controlador, rompiendo el bucle por completo.Aplica los siguientes cambios en tus recursos para solucionarlo de inmediato:📝 1. Modifica tus API ResourcesEn app/Http/Resources/PostResource.phpEnvuelve la inicialización del UserResource dentro de un condicional whenLoaded:phppublic function toArray($request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'body' => $this->body,
        // Solo renderiza el usuario si se cargó previamente en el controlador
        'user' => new UserResource($this->whenLoaded('user')), 
    ];
}
Usa el código con precaución.En app/Http/Resources/UserResource.phpHaz exactamente lo mismo para la colección de posts del usuario:phppublic function toArray($request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'email' => $this->email,
        // Solo renderiza la colección si se cargó explícitamente con ->with('posts')
        'posts' => PostResource::collection($this->whenLoaded('posts')),
    ];
}
Usa el código con precaución.🕹️ 2. Cómo controlar qué se muestra desde tu ControladorA partir de ahora, tú tienes el control absoluto de qué recursos anidan a otros utilizando el método with() en las consultas de Eloquent.Caso A: Quieres ver el Post con su Usuario (pero SIN volver a cargar los posts del usuario)En tu PostController, utiliza with('user'):phppublic function show(Post $post)
{
    // Carga la relación 'user'. PostResource la pintará, pero se detendrá ahí.
    return new PostResource($post->load('user'));
}
Usa el código con precaución.Caso B: Quieres ver el Usuario con sus Posts (pero SIN volver a cargar el usuario dentro de cada post)En tu controlador de usuarios (o perfil):phppublic function showProfile(Request $request)
{
    $user = $request->user()->load('posts');
    
    // UserResource pintará los posts, pero PostResource no pintará el 'user' de nuevo
    return new UserResource($user);
}
Usa el código con precaución.💡 Alternativa Avanzada: Recursos especializados por EndpointSi en el futuro necesitas que un endpoint siempre devuelva la relación de forma obligatoria sin depender de with(), la buena práctica en APIs profesionales es no reutilizar el mismo recurso. Puedes crear un recurso plano secundario, por ejemplo PostSimpleResource, que omita por completo el campo 'user', rompiendo la dependencia estructural desde el código.¿Funcionó este cambio y se detuvo la caída de la conexión en Postman? Si lo deseas, podemos implementar Paginación eficiente junto a estas relaciones para que tu API procese altos volúmenes de datos con un consumo mínimo de memoria.7 sitiosLaravel API Resources with Relations: Methods to Avoid N+1 QueryLaravel has API Resources but, loaded with relationships, they may cause performance issues. There are conditional methods to help...Laravel DailyIssue with Eloquent: API Resources - Conditional RelationshipsI think I might have found a bug in Laravel, please see the below code: I am hitting an endpoint which is /api/users/1 public func...LaracastsLaravel API Resource with Relationships | Laravel 9 TutorialThis video demonstrates how to work with API resources and relationships in Laravel. It shows how to display all posts with their ...8:37YouTube·Tony XhepaHow to avoid circular dependencies? - LaracastsWhat is the best practice to avoid circular dependencies? I had this problem with Javascript but it also aplies to PHP and Laravel...LaracastsDynamic API Response Control in Laravel ResourcesLaravel's API Resources provide elegant methods for conditionally including attributes in your responses, allowing you to create f...Laravel NewsMostrar todoDijiste como quedaria el endpoint /users, con todos los posts de cada uno11 de junio de 2026Para el endpoint /users con todos sus posts asociados de forma masiva, la mejor práctica es utilizar Eager Loading (with('posts')) junto con paginación. Esto evita el problema de las consultas repetitivas a la base de datos (problema de N+1) y controla el consumo de memoria del servidor.Debido a que configuraste $this->whenLoaded('posts') en el paso anterior, los posts se incluirán de forma automática y limpia únicamente en este endpoint.Aquí tienes la estructura exacta de cómo debe quedar tu código:🕹️ 1. El Controlador (UserController.php)Puedes crear un controlador específico para los usuarios si aún no lo tienes (php artisan make:controller UserController). Su método index debe estructurarse así:phpnamespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // 1. Cargamos la relación 'posts' de golpe para todos los usuarios
        // 2. Paginamos los resultados (ej. 10 usuarios por página)
        $users = User::with('posts')->paginate(10);

        // 3. Retornamos la colección formateada por el recurso
        return UserResource::collection($users);
    }
}
Usa el código con precaución.📝 2. Definición de la Ruta (routes/api.php)Asegúrate de registrar la ruta correspondiente. Es muy recomendable protegerla con Sanctum para que solo usuarios autenticados puedan ver el listado:phpuse App\Http\Controllers\UserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
Usa el código con precaución.🔬 3. Resultado Estructurado en Postman (Estructura JSON)Al consumir el endpoint GET /users, Laravel generará de forma automática una estructura limpia que incluye los datos del usuario, su lista de posts individualizada y los metadatos de paginación al final de la respuesta, sin caer en bucles:json{
    "data": [
        {
            "id": 1,
            "name": "Test User",
            "email": "test@example.com",
            "posts": [
                {
                    "id": 5,
                    "title": "Mi primer Chirp",
                    "body": "Contenido del post..."
                },
                {
                    "id": 12,
                    "title": "Actualización de desarrollo",
                    "body": "Laravel 13 es genial."
                }
            ]
        },
        {
            "id": 2,
            "name": "Maria Doe",
            "email": "maria@example.com",
            "posts": [] 
        }
    ],
    "links": {
        "first": "http://chirper.test",
        "last": "http://chirper.test",
        "prev": null,
        "next": "http://chirper.test"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 3,
        "per_page": 10,
        "to": 10,
        "total": 28
    }
}
Usa el código con precaución.Como puedes observar, los objetos dentro del arreglo "posts" no vuelven a incluir la propiedad "user", deteniendo el problema del ECONNRESET definitivamente.