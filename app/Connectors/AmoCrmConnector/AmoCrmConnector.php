<?php

class AmoCrmConnector
{
    private $tokensPath;

    public function __construct($tokensPath)
    {
        $this->tokensPath = $tokensPath;

        if (!$tokensPath || !file_exists($tokensPath) || !file_get_contents($tokensPath)) {
            throw new Exception('Unable to get amocrm tokens');
        }

        $tokens = json_decode(file_get_contents($tokensPath), true);

        if (!$tokens) {
            throw new Exception('Unable to read json file with amocrm tokens: ' . $tokensPath);
        }

        $expiration_date = $tokens['expiration_date'];
        $access_token = $tokens['access_token'];
        $refresh_token = $tokens['refresh_token'];

        // проверяем наличие токенов и не просрочились ли они, если всё ок, обновление токенов не требуется
        if ($access_token && $refresh_token && $expiration_date > time()) {
            return;
        }

        $link = 'https://' . SUBDOMAIN . '.amocrm.ru/oauth2/access_token';

        $data = [
            'client_id' => CLIENT_ID,
            'client_secret' => CLIENT_SECRET,
            'redirect_uri' => REDIRECT_URI,
        ];

        if ($refresh_token && $expiration_date < time()) {
            // need to refresh access token
            $grant_type = 'refresh_token';
            $data['refresh_token'] = $refresh_token;
        } else {
            // need get access token
            $grant_type = 'authorization_code';
            $data['code'] = AUTHORIZATION_CODE;
        }

        $data['grant_type'] = $grant_type;

        $out = $this->sendCurlRequest($link, $data);

        $response = json_decode($out, true);

        $access_token = $response['access_token']; //Access токен
        $refresh_token = $response['refresh_token']; //Refresh токен
        $expires_in = $response['expires_in']; //Refresh токен

        $data = [
            "access_token" => $access_token,
            "refresh_token" => $refresh_token,
            "expiration_date" => time() + $expires_in,
        ];

        file_put_contents($tokensPath, json_encode($data));
    }

    /**
     * @param string $leadName
     * @param string $price
     * @param array $customFields
     * @param string $statusId
     * @return int
     * @throws Exception
     */
    public function createLead($leadName, $price, $customFields = [], $statusId = null)
    {
        $leads[] = [
            'name' => $leadName,
            'price' => $price,
            'custom_fields_values' => $customFields,
            'status_id' => (int)$statusId,
        ];

        $link = 'https://' . SUBDOMAIN . '.amocrm.ru/api/v4/leads';
        $tokens = json_decode(file_get_contents($this->tokensPath), true);
        $access_token = $tokens['access_token'];
        $out = $this->sendCurlRequest($link, $leads, ['Authorization: Bearer ' . $access_token]);

        $response = json_decode($out, true);

        return $response['_embedded']['leads'][0]['id'];
    }

    /**
     * @param $orderId
     * @param $contactName
     * @param $customFields
     * @return mixed
     * @throws Exception
     */
    public function createContact($orderId, $contactName, $customFields = [])
    {
        $contacts['add'] = [
            [
                'name' => $contactName,
                'created_at' => (new DateTime())->format('U'),
                'leads_id' => [
                    (string)$orderId,
                ],
                'custom_fields' => $customFields,
            ],
        ];

        $link = 'https://' . SUBDOMAIN . '.amocrm.ru/api/v2/contacts';

        $tokens = json_decode(file_get_contents($this->tokensPath), true);
        $access_token = $tokens['access_token'];
        $this->sendCurlRequest($link, $contacts, ['Authorization: Bearer ' . $access_token]);

        return;
    }

    /**
     * @param $amoLeadId
     * @param $message
     * @return null
     * @throws Exception
     */
    public function createNote($amoLeadId, $message)
    {
        $data[] = [
            'note_type' => 'common',
            'entity_id' => $amoLeadId,
            "params" => [
                "text" => $message,
            ],
        ];

        $link = 'https://' . SUBDOMAIN . ".amocrm.ru/api/v4/leads/notes";

        if (empty($data)) {
            return null;
        }

        $tokens = json_decode(file_get_contents($this->tokensPath), true);
        $access_token = $tokens['access_token'];
        $this->sendCurlRequest($link, $data, ['Authorization: Bearer ' . $access_token]);

        return;
    }

    /**
     * @param $amoLeadId
     * @param $message
     * @param $completeTill
     * @param int $responsibleUserId
     */
    public function createTask($amoLeadId, $message, $completeTill, $responsibleUserId = MANAGER_ID)
    {
        $data[] = [
            "responsible_user_id" => $responsibleUserId,
            "complete_till" => $completeTill,
            'entity_id' => $amoLeadId,
            "entity_type" => "leads",
            "text" => $message,
        ];

        $link = 'https://' . SUBDOMAIN . ".amocrm.ru/api/v4/tasks";

        $tokens = json_decode(file_get_contents($this->tokensPath), true);
        $access_token = $tokens['access_token'];
        $this->sendCurlRequest($link, $data, ['Authorization: Bearer ' . $access_token]);

        return;
    }

    public function getFieldsInfo()
    {
        $link = 'https://' . SUBDOMAIN . '.amocrm.ru/api/v4/leads/custom_fields';

        $tokens = json_decode(file_get_contents($this->tokensPath), true);
        $access_token = $tokens['access_token'];
        $out = $this->sendCurlRequest($link, [], ['Authorization: Bearer ' . $access_token], 'GET');

        return json_decode($out, true);
    }

    /**
     * @param $id
     * @return array
     */
    public function getLead($id)
    {
        $link = 'https://' . SUBDOMAIN . '.amocrm.ru/api/v4/leads/' . $id;
        $tokens = json_decode(file_get_contents($this->tokensPath), true);
        $access_token = $tokens['access_token'];
        $out = $this->sendCurlRequest($link, [], ['Authorization: Bearer ' . $access_token], 'GET');

        return json_decode($out, true);
    }

    /**
     * @param $id
     * @return array
     */
    public function getLeads($query)
    {
        $link = 'https://' . SUBDOMAIN . '.amocrm.ru/api/v4/leads?' . $query;

        $tokens = json_decode(file_get_contents($this->tokensPath), true);
        $access_token = $tokens['access_token'];
        $out = $this->sendCurlRequest($link, [], ['Authorization: Bearer ' . $access_token], 'GET');

        return json_decode($out, true);
    }

    /**
     * @param $taskType
     * @param $userId
     * @return mixed
     */
    public function getTasks($taskType, $userId)
    {
        $link = 'https://' . SUBDOMAIN . '.amocrm.ru/api/v2/tasks?filter[status][]=0&filter[task_type][]=' . $taskType . '&responsible_user_id=' . $userId;

        $tokens = json_decode(file_get_contents($this->tokensPath), true);
        $access_token = $tokens['access_token'];
        $out = $this->sendCurlRequest($link, [], ['Authorization: Bearer ' . $access_token], 'GET');

        return json_decode($out, true);
    }

    public function getNotes($leadId)
    {
        $link = 'https://' . SUBDOMAIN . ".amocrm.ru/api/v4/leads/$leadId/notes";

        $tokens = json_decode(file_get_contents($this->tokensPath), true);
        $access_token = $tokens['access_token'];
        $out = $this->sendCurlRequest($link, [], ['Authorization: Bearer ' . $access_token], 'GET');

        return json_decode($out, true);
    }

    /**
     * @param $id
     * @return array
     */
    public function getContactInfo($id)
    {
        $link = 'https://' . SUBDOMAIN . '.amocrm.ru/api/v4/contacts/' . $id;

        $tokens = json_decode(file_get_contents($this->tokensPath), true);
        $access_token = $tokens['access_token'];
        $out = $this->sendCurlRequest($link, [], ['Authorization: Bearer ' . $access_token], 'GET');

        return json_decode($out, true);
    }

    /**
     * @param $id
     * @return array
     */
    public function getCompanyInfo($id)
    {
        $link = 'https://' . SUBDOMAIN . '.amocrm.ru/api/v4/companies/' . $id;

        $tokens = json_decode(file_get_contents($this->tokensPath), true);
        $access_token = $tokens['access_token'];
        $out = $this->sendCurlRequest($link, [], ['Authorization: Bearer ' . $access_token], 'GET');

        return json_decode($out, true);
    }

    public function updateTasks($task)
    {
        $data['update'][] = $task;

        $tokens = json_decode(file_get_contents($this->tokensPath), true);
        $access_token = $tokens['access_token'];
        $link = 'https://' . SUBDOMAIN . '.amocrm.ru/api/v2/tasks';
        $this->sendCurlRequest($link, $data, ['Authorization: Bearer ' . $access_token]);

        return;
    }

    public function updateLeads($lead)
    {
        $data[] = $lead;

        $link = 'https://' . SUBDOMAIN . '.amocrm.ru/api/v4/leads';

        $tokens = json_decode(file_get_contents($this->tokensPath), true);
        $access_token = $tokens['access_token'];
        $out = $this->sendCurlRequest($link, $data, ['Authorization: Bearer ' . $access_token], "PATCH");

        return;
    }

    public function updateField($fieldId, $data)
    {
        $link = 'https://' . SUBDOMAIN . '.amocrm.ru/api/v4/leads/custom_fields/' . $fieldId;

        $tokens = json_decode(file_get_contents($this->tokensPath), true);
        $access_token = $tokens['access_token'];
        $out = $this->sendCurlRequest($link, $data, ['Authorization: Bearer ' . $access_token], "PATCH");

        return;
    }

    /**
     * @param $link
     * @param $data
     * @param array $header
     * @param string $method
     * @return bool|string
     */
    private function sendCurlRequest($link, $data, $header = [], $method = 'POST')
    {
        $curl = curl_init();

        $defaultHeader = ['Content-Type: application/json'];

        $header = array_merge($header, $defaultHeader);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $code = (int)$code;

        $errors = [
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        ];

        try {
            if ($code < 200 || $code > 204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
            }
        } catch (Exception $e) {
            (new Logger(DEBUG_PATH))
                ->debug('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode() . ' ' . json_encode($out));
        }

//        $logger = (new Logger(LOGS_PATH));
//        $logger->info("Amocrm response $link");
//        $logger->info(json_encode($out));

        return $out;
    }
}
