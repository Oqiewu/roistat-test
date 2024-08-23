<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/AmoCRM.php';

class AmoCRMController
{
    private $amoCRM;

    public function __construct()
    {
        $this->amoCRM = new AmoCRM(
            $_SESSION['access_token'],
            $GLOBALS['subdomain'],
            $GLOBALS['pipelineId'],
            $GLOBALS['statusId'],
            $GLOBALS['customFieldId'],
            $GLOBALS['phoneFieldId'],
            $GLOBALS['emailFieldId']
        );
    }

    public function handleCreateDeal($data)
    {
        try {
            $contactId = $this->amoCRM->createContact($data);
            $dealId = $this->amoCRM->createDeal($contactId, $data);

            return [
                'status' => 'success',
                'message' => 'Заявка успешно отправлена',
                'deal_id' => $dealId
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка при отправке заявки',
                'error' => $e->getMessage()
            ];
        }
    }
}
