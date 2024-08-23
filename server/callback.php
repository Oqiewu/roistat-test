<?php

require_once __DIR__ . '/config.php';
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$action = $_GET['action'] ?? null;

if ($action === 'refresh_token') {
    if (isset($_SESSION['refresh_token'])) {
        handleRefreshToken($subdomain, $clientId, $clientSecret, $redirectUri, $_SESSION['refresh_token']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Refresh token не найден в сессии']);
    }
} elseif ($action === 'check_token') {
    handleCheckToken($subdomain, $clientId, $clientSecret, $redirectUri);
} elseif (isset($_SESSION['access_token'])) {
    handleAuthorizationCode($_GET['code'], $subdomain, $clientId, $clientSecret, $redirectUri);
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Не указано действие']);
    exit;
}

function handleRefreshToken($subdomain, $clientId, $clientSecret, $redirectUri, $refreshToken) {
    $client = new Client(['verify' => false]);
    $tokenUrl = "https://{$subdomain}.amocrm.ru/oauth2/access_token";

    $postData = [
        'form_params' => [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'redirect_uri' => $redirectUri,
        ]
    ];

    try {
        $response = $client->post($tokenUrl, $postData);
        $responseBody = json_decode($response->getBody(), true);

        if (!isset($responseBody['access_token'])) {
            throw new Exception('Не удалось обновить access_token');
        }

        $_SESSION['access_token'] = $responseBody['access_token'];
        $_SESSION['refresh_token'] = $responseBody['refresh_token'];
        updateTokensFile($responseBody['access_token'], $responseBody['refresh_token']);

        echo json_encode(['status' => 'success', 'message' => 'Токен успешно обновлен']);
    } catch (RequestException $e) {
        handleError('Ошибка при обновлении токена', $e);
    }
}

function handleCheckToken($subdomain, $clientId, $clientSecret, $redirectUri) {
    if (!isset($_SESSION['access_token'])) {
        echo json_encode(['status' => 'error', 'message' => 'Access token не найден в сессии']);
        return;
    }

    $client = new Client(['verify' => false]);
    $testUrl = "https://{$subdomain}.amocrm.ru/api/v4/account";

    try {
        $response = $client->get($testUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $_SESSION['access_token']
            ]
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Токен действителен', 'tokenUpdated' => false]);
    } catch (RequestException $e) {
        if ($e->getResponse()->getStatusCode() === 401) {
            handleRefreshToken($subdomain, $clientId, $clientSecret, $redirectUri, $_SESSION['refresh_token']);
            echo json_encode(['status' => 'success', 'message' => 'Токен обновлен автоматически', 'tokenUpdated' => true]);
        } else {
            handleError('Ошибка при проверке токена', $e);
        }
    }
}

function handleAuthorizationCode($authCode, $subdomain, $clientId, $clientSecret, $redirectUri) {
    $client = new Client();
    $tokenUrl = "https://{$subdomain}.amocrm.ru/oauth2/access_token";

    $postData = [
        'form_params' => [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $authCode,
            'redirect_uri' => $redirectUri,
        ]
    ];

    try {
        $response = $client->post($tokenUrl, $postData);
        $responseBody = json_decode($response->getBody(), true);

        if (!isset($responseBody['access_token'])) {
            throw new Exception('Не удалось получить access_token');
        }

        $_SESSION['access_token'] = $responseBody['access_token'];
        $_SESSION['refresh_token'] = $responseBody['refresh_token'];
        updateTokensFile($responseBody['access_token'], $responseBody['refresh_token']);

        header('Location: /index.php');
        exit;
    } catch (RequestException $e) {
        handleError('Ошибка при получении токена', $e);
    }
}

function updateTokensFile($accessToken, $refreshToken) {
    $tokensFile = __DIR__ . '/tokens.txt';
    
    if (file_exists($tokensFile)) {
        $tokens = file($tokensFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        $tokensArray = [];
        foreach ($tokens as $tokenLine) {
            list($key, $value) = explode('=', $tokenLine, 2);
            $tokensArray[trim($key)] = trim($value);
        }
        
        $tokensArray['ACCESS_TOKEN'] = $accessToken;
        $tokensArray['REFRESH_TOKEN'] = $refreshToken;
        
        $newTokensContent = '';
        foreach ($tokensArray as $key => $value) {
            $newTokensContent .= "{$key}={$value}\n";
        }
        
        file_put_contents($tokensFile, $newTokensContent);
    } else {
        $newTokensContent = "ACCESS_TOKEN={$accessToken}\nREFRESH_TOKEN={$refreshToken}\n";
        file_put_contents($tokensFile, $newTokensContent);
    }
}

function handleError($message, $exception) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $message,
        'error' => $exception->getMessage()
    ]);
    exit;
}
