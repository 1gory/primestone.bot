<?php

class AmoCrmData
{
    public static function getTasksData($userName)
    {
        $connector = new AmoCrmConnector(AMOCRM_TOKENS_PATH);

        $googleCalendarTasks = GoogleCalendarData::getTasks($userName);

        $query = "filter[statuses][1][pipeline_id]=" . MAIN_PIPELINE_ID . "&filter[statuses][1][status_id]=" . MEASUREMENT_DATE_AGREED_STATUS_ID
            . "&filter[statuses][2][pipeline_id]=" . MAIN_PIPELINE_ID . "&filter[statuses][2][status_id]=" . INSTALLATION_STATUS_ID
            . "&filter[statuses][3][pipeline_id]=" . MAIN_PIPELINE_ID . "&filter[statuses][3][status_id]=" . REPEATED_INSTALLATION_STATUS_ID
            . "&with=contacts"
        ;

        $leads = $connector->getLeads($query);

        if (empty($leads) || empty($leads['_embedded']) || empty($googleCalendarTasks)) {
            return [
                'text' => "Задачи на сегодня и завтра отсутсвтуют",
                'leadsIds' => [],
            ];
        }

        // фильтруем лиды
        $filteredAmoLeads = [];
        foreach ($leads['_embedded']['leads'] as $lead) {
            if (empty($lead['custom_fields_values'])) {
                continue;
            }

            // проверка назначенного замерщика в статусе "Дата замера согласована"
            if ($lead['status_id'] === MEASUREMENT_DATE_AGREED_STATUS_ID) {
                $measurerName = self::getCustomField($lead['custom_fields_values'], 'Замерщик');
                if ($measurerName !== $userName) {
                    continue;
                }
                $lead['type'] = 'measurement';
            }

            // проверка назначенного монтажника в статусе "Монтаж"
            if ($lead['status_id'] === INSTALLATION_STATUS_ID || $lead['status_id'] === REPEATED_INSTALLATION_STATUS_ID) {
                $installerName = self::getCustomField($lead['custom_fields_values'], 'Монтажник');
                if ($installerName !== $userName) {
                    continue;
                }
                $lead['type'] = 'installation';
            }

            $filteredAmoLeads[] = $lead;
        }

        $today = date('d.m.y', strtotime('00:00'));
        $tomorrow = date('d.m.y', strtotime('tomorrow 00:00 + 1days'));
        $tasksText = "Задачи на сегодня и завтра\r\n($today - $tomorrow):\r\n\r\n";

        if (empty($filteredAmoLeads) || empty($googleCalendarTasks)) {
            return [
                'text' => "Задачи на сегодня и завтра отсутсвтуют",
                'leadsIds' => [],
                'googleEventsIds' => [],
            ];
        }

        $key = 0;
        $leadsIds = [];
        $leadsType = [];
        $googleEventsIds = [];
        foreach ($googleCalendarTasks as $googleTask) {
            $amoId = $googleTask['amoId'];

            $dateStart = DateTime::createFromFormat(DateTime::ISO8601, $googleTask['startTime'])
                ->setTimezone(new DateTimeZone('Europe/Moscow'));

            $dateEnd = DateTime::createFromFormat(DateTime::ISO8601, $googleTask['endTime'])
                ->setTimezone(new DateTimeZone('Europe/Moscow'));

            foreach ($filteredAmoLeads as $lead) {

                if ($lead['id'] != $amoId) {
                    continue;
                }

                $leadsIds[] = $lead['id'];
                $leadsType[] = $lead['type'];
                $googleEventsIds[] = $googleTask['eventId'];

                $type = '';
                $preliminarilyCost = '';
                $surcharge = '';
                $installationComment = '';
                $measurementComment = '';
                if ($lead['status_id'] === MEASUREMENT_DATE_AGREED_STATUS_ID) {
                    $type = 'Замер';
                    $measurementComment = self::getCustomField($lead['custom_fields_values'], 'Комментарии для замерщика');
                    $preliminarilyCost = (int) self::getCustomField($lead['custom_fields_values'], 'Предварительная стоимость');
                } elseif ($lead['status_id'] === INSTALLATION_STATUS_ID) {
                    $type = 'Монтаж';
                    $installationComment = self::getCustomField($lead['custom_fields_values'], 'Комментарии для монтажника');
                    $surcharge = (int) self::getCustomField($lead['custom_fields_values'], 'Должны доплатить на монтаже');
                }

                $tasksText .= '🛑 ' . ++$key . '. ' . $type . ' ' .$lead['name'] . "\r\n";
                $tasksText .= '*Номер сделки*: ' . $lead['id'] . "\r\n";

                $tasksText .= '📆 *Дата: ' . $dateStart->format('d.m') . '*' . "\r\n";
                $tasksText .= '⏰ *Время*: ' . $dateStart->format('H.i') . '-' . $dateEnd->format('H.i') . "\r\n";

                if (!empty($lead['_embedded']) && !empty($lead['_embedded']['contacts']) && !empty($lead['_embedded']['contacts'][0])) {
                    $contactId = $lead['_embedded']['contacts'][0]['id'];
                    $res = $connector->getContactInfo($contactId);
                    $tasksText .= '*Имя клиента*: ' . $res['name'] . "\r\n";
                    $tasksText .= '*Телефон*: ' . self::getCustomField($res['custom_fields_values'], 'Телефон') . "\r\n";
                }

                $tasksText .= '*Адрес клиента*: ' . self::getCustomField($lead['custom_fields_values'], 'Адрес клиента') . "\r\n";

                $tasksText .= $preliminarilyCost ? '*Предварительная стоимость*: ' . $preliminarilyCost . "\r\n" : '';
                $tasksText .= $surcharge ? '*Доплата на монтаже*: ' . $surcharge . "\r\n" : '';

                $tasksText .= '*Комментарий*: ' . $measurementComment . $installationComment  . "\r\n";

                $tasksText .= "\r\n";
            }
        }

        return [
            'text' => $tasksText,
            'leadsIds' => $leadsIds,
            'leadsType' => $leadsType,
            'googleEventsIds' => $googleEventsIds,
        ];
    }

    private static function getCustomField($customFields, $key)
    {
        $value = null;
        foreach ($customFields as $field) {
            if ($field['field_name'] === $key) {
                $value = $field['values'][0]['value'];
            }
        }

        return $value;
    }
}
