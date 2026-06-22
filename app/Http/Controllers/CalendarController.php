<?php

namespace App\Http\Controllers;

use App\Http\Services\CalendarService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct(private CalendarService $calendarService) {}

    public function redirectCalendar(Request $request)
    {
        if (!$request->input('code')) {
            return redirect($this->calendarService->getAuthUrl());
        }

        $this->calendarService->handleCallback($request->input('code'));
        return redirect()->route('createEvent');
    }

    public function createEvent(Request $request)
    {
        // Validación robusta de la sesión/token mediante el servicio
        if (!$this->calendarService->isClientAuthenticated()) {
            return redirect()->route('redirectUri');
        }

        try {
            // Obtenemos los datos limpios de la API
            $calendarsData = $this->calendarService->getCalendarsAndEvents();
            
            // Insertamos el nuevo evento y guardamos su enlace
            $eventLink = $this->calendarService->insertNewEvent();

            // Retornamos las respuestas de manera nativa (puede cambiarse por una vista de Blade)
            return response()->json([
                'status' => 'success',
                'message' => '¡Evento creado con éxito!',
                'view_link' => $eventLink,
                'current_data' => $calendarsData
            ]);

        } catch (\Google\Service\Exception $e) {
            return response()->json(['error' => 'API de Google falló: ' . $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error de servidor: ' . $e->getMessage()], 500);
        }
    }
}
