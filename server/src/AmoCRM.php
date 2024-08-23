<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AmoCRM
{
    private $client;
    private $accessToken;
    private $subdomain;
    private $pipelineId;
    private $statusId;
    private $customFieldId;
    private $phoneFieldId;
    private $emailFieldId;

    public function __construct($accessToken, $subdomain, $pipelineId, $statusId, $customFieldId, $phoneFieldId, $emailFieldId)
    {
        $this->accessToken = $accessToken;
        $this->subdomain = $subdomain;
        $this->pipelineId = $pipelineId;
        $this->statusId = $statusId;
        $this->customFieldId = $customFieldId;
        $this->phoneFieldId = $phoneFieldId;
        $this->emailFieldId = $emailFieldId;
        
        $this->client = new Client([
            'base_uri' => "https://$subdomain.amocrm.ru/api/v4/",
            'headers' => [
                'Authorization' => "Bearer $accessToken",
                'Content-Type' => 'application/json',
            ],
            'verify' => false,
        ]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createContact($data)
    {
        try {
            $response = $this->client->post('contacts', [
                'json' => [
                    [
                        'first_name' => $data['name'],
                        'custom_fields_values' => [
                            [
                                'field_id' => (int)$this->phoneFieldId,
                                'values' => [
                                    [
                                        'value' => $data['phone'],
                                        'enum_code' => 'HOME',
                                    ]
                                ]
                            ],
                            [
                                'field_id' => (int)$this->emailFieldId,
                                'values' => [
                                    [
                                        'value' => $data['email'],
                                        'enum_code' => 'WORK',
                                    ]
                                ]
                            ],
                            [
                                'field_id' => (int)$this->customFieldId,
                                'values' => [
                                    [
                                        'value' => $data['time_spent'],
                                    ]
                                ]
                            ]
                        ]
                    ],                
                ]
            ]);

            $contact = json_decode($response->getBody(), true);
            return $contact['_embedded']['contacts'][0]['id'];
        } catch (RequestException $e) {
            throw new Exception('Ошибка при создании контакта: ' . $e->getMessage());
        }
    }

    public function createDeal($contactId, $data)
    {
        try {
            $response = $this->client->post('leads', [
                'json' => [
                    [
                        'name' => $data['name'],
                        'price' => (int)$data['price'],
                        'pipeline_id' => (int)$this->pipelineId,
                        'status_id' => (int)$this->statusId,
                        '_embedded' => [
                            'contacts' => [
                                [
                                    'id' => (int)$contactId,
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    
            $deal = json_decode($response->getBody(), true);
            return $deal['_embedded']['leads'][0]['id'];
        } catch (RequestException $e) {
            $responseBody = $e->hasResponse() ? (string) $e->getResponse()->getBody() : 'No response';
            throw new Exception('Ошибка при создании сделки: ' . $e->getMessage() . ' Response: ' . $responseBody);
        }
    } 
}