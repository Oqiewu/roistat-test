<?php
require_once __DIR__ . '/vendor/autoload.php';
session_start();

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$tokensFile = __DIR__ . '/tokens.txt';

$tokens = file($tokensFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$tokensArray = [];
foreach ($tokens as $tokenLine) {
    list($key, $value) = explode('=', $tokenLine, 2);
    $tokensArray[trim($key)] = trim($value);
}

$_SESSION['access_token'] = $tokensArray['ACCESS_TOKEN'] ?? null;
$_SESSION['refresh_token'] = $tokensArray['REFRESH_TOKEN'] ?? null;

$subdomain = $_ENV['SUBDOMAIN'];
$pipelineId = $_ENV['PIPELINE_ID'];
$statusId = $_ENV['STATUS_ID'];
$customFieldId = $_ENV['CUSTOM_FIELD_ID'];
$phoneFieldId = $_ENV['PHONE_FIELD_ID'];
$emailFieldId = $_ENV['EMAIL_FIELD_ID'];
$clientId = $_ENV['CLIENT_ID'];
$clientSecret = $_ENV['CLIENT_SECRET'];
$redirectUri = $_ENV['REDIRECT_URI'];
