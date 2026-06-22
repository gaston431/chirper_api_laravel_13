<?php

namespace App\Http\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\EventAttendee;
use Google\Service\Calendar\EventReminders;
use Google\Service\Calendar\EventReminder;

class CalendarService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(storage_path('app/client_secrets.json'));
        $this->client->setRedirectUri(route('redirectUri'));
        $this->client->addScope(Calendar::CALENDAR);
        $this->client->setAccessType('offline');  
        // Eliminamos 'force' para producción; cámbialo a 'consent' si necesitas forzar login en pruebas
        $this->client->setPrompt('consent'); 
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function handleCallback(string $code): void
    {
        $this->client->fetchAccessTokenWithAuthCode($code);
        session(['access_token' => $this->client->getAccessToken()]);
    }

    public function isClientAuthenticated(): bool
    {
        if (!session()->has('access_token')) {
            return false;
        }

        $this->client->setAccessToken(session('access_token'));

        if ($this->client->isAccessTokenExpired()) {
            if ($this->client->getRefreshToken()) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                session(['access_token' => $newToken]);
                return true;
            }
            return false;
        }

        return true;
    }

    public function getCalendarsAndEvents(): array
    {
        $service = new Calendar($this->client);
        $calendarList = $service->calendarList->listCalendarList();
        $data = [];

        if ($calendarList->getItems()) {
            foreach ($calendarList->getItems() as $calendarListEntry) {
                $calendarId = $calendarListEntry->getId();
                
                $optParams = [
                    'maxResults' => 10,
                    'singleEvents' => true,
                    'orderBy' => 'startTime'
                ];
                
                $eventsList = $service->events->listEvents($calendarId, $optParams);
                $events = [];

                if ($eventsList->getItems()) {
                    foreach ($eventsList->getItems() as $event) {
                        $events[] = $event->getSummary();
                    }
                }

                $data[] = [
                    'calendar' => $calendarListEntry->getSummary(),
                    'events' => $events
                ];
            }
        }

        return $data;
    }

    public function insertNewEvent(): string
    {
        $service = new Calendar($this->client);
        
        $event = new Event();
        $event->setSummary('Google I/O 2026');
        $event->setDescription('A chance to hear more about Google\'s developer products.');

        $start = new EventDateTime();
        $start->setDateTime('2026-06-19T09:00:00-03:00'); 
        $start->setTimeZone('America/Argentina/Buenos_Aires');
        $event->setStart($start);

        $end = new EventDateTime();
        $end->setDateTime('2026-06-19T17:00:00-03:00'); 
        $end->setTimeZone('America/Argentina/Buenos_Aires');
        $event->setEnd($end);

        $attendee1 = new EventAttendee();
        $attendee1->setEmail('gastondicriscio@gmail.com');
        $attendee2 = new EventAttendee();
        $attendee2->setEmail('gaston_431@hotmail.com');
        $event->setAttendees([$attendee1, $attendee2]);

        $reminders = new EventReminders();
        $reminders->setUseDefault(false);

        $override1 = new EventReminder();
        $override1->setMethod('email');
        $override1->setMinutes(1440);

        $override2 = new EventReminder();
        $override2->setMethod('popup');
        $override2->setMinutes(10);

        $reminders->setOverrides([$override1, $override2]);
        $event->setReminders($reminders);

        $eventCreated = $service->events->insert('primary', $event);
        return $eventCreated->getHtmlLink();
    }
}
