<?php
// File: admin/poll/twitch_livestreams.php

declare(strict_types=1);

# Must include here because DH runs FastCGI https://www.phind.com/search?cache=zfj8o8igbqvaj8cm91wp1b7k
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

$config = new Config();
$twitchClientId = $config->twitch_client_id ?? null;
$twitchClientSecret = $config->twitch_client_secret ?? null;
$twitchUserName = $config->twitch_user_name ?? null;

if (!$twitchClientId || !$twitchClientSecret || !$twitchUserName) {
    die("Twitch credentials missing from config.");
}

use Database\LivestreamFactory;

/**
 * Exchange client credentials for an app access token.
 * Returns ['access_token' => string, 'expires_in' => int]
 */
function getAppAccessToken(string $clientId, string $clientSecret): array
{
    $url = 'https://id.twitch.tv/oauth2/token';
    $post = http_build_query([
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'grant_type' => 'client_credentials',
    ]);

    $ch = curl_init("$url?$post");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_TIMEOUT => 10,
    ]);

    $resp = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('Token fetch error: ' . curl_error($ch));
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Failed to get Twitch token, status $httpCode: $resp");
    }

    $data = json_decode($resp, true);
    if (!$data || empty($data['access_token'])) {
        throw new Exception('Invalid token response: ' . $resp);
    }

    return [
        'access_token' => $data['access_token'],
        'expires_in' => $data['expires_in'],
    ];
}

// 1) Obtain a fresh App Access Token
$tokenData = getAppAccessToken($twitchClientId, $twitchClientSecret);
$twitchAccessToken = $tokenData['access_token'];
$expiresAt = time() + $tokenData['expires_in'];  // optionally store for caching

// 2) Resolve user_id if provided as login name
$urlUser = "https://api.twitch.tv/helix/users?login={$twitchUserName}";
$resUser = fetchTwitch($urlUser, $twitchClientId, $twitchAccessToken);
if (empty($resUser['data'][0]['id'])) {
    throw new Exception("Invalid Twitch user login: {$twitchUserName}");
}
$twitchUserID = $resUser['data'][0]['id'];

function fetchTwitch(string $url, string $clientId, string $accessToken): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Client-ID: $clientId",
            "Authorization: Bearer $accessToken",
        ],
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        print_rob($response);
        throw new Exception("Twitch API returned status $http_code");
    }

    return json_decode($response, true);
}

$url = "https://api.twitch.tv/helix/videos?user_id=$twitchUserID&type=archive";
$data = fetchTwitch($url, $twitchClientId, $twitchAccessToken);

$results = [];
foreach ($data['data'] as $item) {
    // print_rob($item);
    $ls = LivestreamFactory::fromApiItem($item, $mla_database, 'twitch');
    if (!$ls->existsInDatabase($ls->getExternalId())) {
        // print_rob($ls);
        $ls->saveToDatabase();
        $results[] = [
            'title' => $ls->getTitle(),
            'status' => '✅ Saved  (and be sure to save this stuff in DB)',
            'url' => 'https://www.twitch.tv/videos/' . $item['id'],
            'thumbnail_url' => str_replace('%{width}x%{height}', '320x180', $item['thumbnail_url']),
            'duration' => $item['duration'],
        ];
    } else {
        $results[] = [
            'title' => $ls->getTitle(),
            'status' => '😊 Already in database BUT THIS IS NOT IN DATABASE need thumbnail 149 characters',
            'url' => 'https://www.twitch.tv/videos/' . $item['id'],
            'thumbnail_url' => str_replace('%{width}x%{height}', '320x180', $item['thumbnail_url']),
            'duration' => $item['duration'],
        ];
    }
}

$page = new \Template(config: $config);
$page->setTemplate(template_file: "admin/poll/twitch_livestreams.tpl.php");
$page->set(name: "results", value: $results);
$page->set(name: "page_title", value: "Twitch Livestream Poll");
$inner = $page->grabTheGoods();

$layout = new \Template(config: $config);
$layout->setTemplate(template_file: "layout/admin_base.tpl.php");
$layout->set(name: "page_title", value: "Twitch Livestream Poll");
$layout->set(name: "page_content", value: $inner);
$layout->echoToScreen();
