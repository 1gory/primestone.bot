<?php

class GoogleCalendarData
{
    static function getClient()
    {
        $client = new Google_Client();
        $client->setApplicationName('Google Calendar API PHP Quickstart');
        $client->setScopes(Google_Service_Calendar::CALENDAR);
        $client->setAuthConfig(CREDENTIALS_DIR . '/google_calendar_credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = CREDENTIALS_DIR . '/google_calendar_token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    static function updateCalendars($usersNames)
    {
        $client = self::getClient();
        $service = new Google_Service_Calendar($client);

        $calendarList = $service->calendarList->listCalendarList();

        $calendarsForAdding = [];
        $calendarId = null;

        $calendarsForDelete = $calendarList['items'];

        foreach ($usersNames as $userName) {
            foreach ($calendarList['items'] as $key => $calendar) {
                if ($calendar['summary'] === $userName) {
                    unset($calendarsForDelete[$key]);
                    continue(2);
                }
            }
            $calendarsForAdding[] = $userName;
        }

        foreach ($calendarsForAdding as $calendarName) {
            $calendar = new Google_Service_Calendar_Calendar();
            $calendar->setSummary($calendarName);
            $calendar->setTimeZone('Europe/Moscow');
            BotLogger::logIt("Создаю календарь $calendarName");
            $service->calendars->insert($calendar);
        }

//        foreach ($calendarsForDelete as $calendar) {
//            $service->calendars->delete($calendar['id']);
//        }
    }

    static function getTasks($userName)
    {
        $client = self::getClient();
        $service = new Google_Service_Calendar($client);

//        $calendarId = 'primary';

        // получить задачи с сегодня 00:00 до завтра 23:59
        $date = (new DateTime());
        $date->setTime(0, 0);
        $today = $date->format(DateTime::ATOM);
        $date->add(new DateInterval('P5D'));
        $date->sub(new DateInterval('PT1S'));
        $tomorrow = $date->format(DateTime::ATOM);

        $optParams = [
            //            'maxResults' => 10,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => $today,
            'timeMax' => $tomorrow,
        ];

        $calendarList = $service->calendarList->listCalendarList();

        $calendarId = null;
        foreach ($calendarList['items'] as $calendar) {
            if ($calendar['summary'] === $userName) {
                $calendarId = $calendar['id'];
                break;
            }
        }

        if (empty($calendarId)) {
            return [];
        }

        $results = $service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();

        $results = [];
        foreach ($events as $event) {
            $results[] = [
                'amoId' => $event->summary,
                'eventId' => $event->id,
                'startTime' => $event->start->dateTime,
                'endTime' => $event->end->dateTime,
            ];
        }

        return $results;
    }

    static function updateTaskColor($userName, $eventId)
    {
        $service = new Google_Service_Calendar(self::getClient());

        $calendarId = self::getCalendarByUsername($userName);
        $event = $service->events->get($calendarId, $eventId);
        $event->setColorId(2); // green

        $service->events->update($calendarId, $event->getId(), $event);
    }

    static function updateTaskTime($calendarName, $eventId, DateTime $timeStart, DateTime $timeEnd)
    {
        $calendarId = self::getCalendarByUsername($calendarName);
        if (!$calendarId) {
            (new Logger(DEBUG_PATH))->debug("Не найден календарь по названию $calendarName");
            return null;
        }
        $service = new Google_Service_Calendar(self::getClient());

        $event = $service->events->get($calendarId, $eventId);

        $event->setStart(
            new Google_Service_Calendar_EventDateTime(
                [
                    'dateTime' => $timeStart->format('c'),
                    'timeZone' => 'Europe/Moscow',
                ]
            )
        );

        $event->setEnd(
            new Google_Service_Calendar_EventDateTime(
                [
                    'dateTime' => $timeEnd->format('c'),
                    'timeZone' => 'Europe/Moscow',
                ]
            )
        );

        $service->events->update($calendarId, $eventId, $event);
    }

    static function deleteTask($userName, $eventId)
    {
        $calendarId = self::getCalendarByUsername($userName);

        $service = new Google_Service_Calendar(self::getClient());
        $service->events->delete($calendarId, $eventId);
    }

    private static function getCalendarByUsername($userName)
    {
        $client = self::getClient();

        $service = new Google_Service_Calendar($client);

        $calendarList = $service->calendarList->listCalendarList();

        $calendarId = null;
        foreach ($calendarList['items'] as $calendar) {
            if ($calendar['summary'] === $userName) {
                $calendarId = $calendar['id'];
                break;
            }
        }

        return $calendarId;
    }
}
