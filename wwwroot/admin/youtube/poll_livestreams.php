<?php
# Must include here because DH runs FastCGI https://www.phind.com/search?cache=zfj8o8igbqvaj8cm91wp1b7k
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";
if (!$is_logged_in->isLoggedIn()) {
    die("You must be logged in to run this script.");
}
$config = new Config();
$channelId = $config->mt3_channel_id;
if (empty($channelId)) {
    die("YouTube channel ID is not set in the configuration.");
}
$apiKey = $config->youtube_key;
if (empty($apiKey)) {
    die("YouTube API key is not set in the configuration.");
}

function fetch_url($url)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        throw new Exception("Non-200 response: $http_code");
    }

    return $response;
}


$url = "https://www.googleapis.com/youtube/v3/search?" . http_build_query([
    'key' => $apiKey,
    'channelId' => $channelId,
    'part' => 'snippet',
    'type' => 'video',
    'eventType' => 'completed',
    'maxResults' => 50,
    'order' => 'date'
]);

$response = fetch_url($url);

$data = json_decode($response, true);

if (!isset($data['items'])) {
    die("No livestream data found or API error.");
}

use Youtube\Livestream;

foreach ($data['items'] as $item) {
    $videoId = $item['id']['videoId'];
    $title = $item['snippet']['title'];
    $description = $item['snippet']['description'];
    $publishedAt = date('Y-m-d H:i:s', strtotime($item['snippet']['publishedAt']));

    print_rob("Processing video: $title ($videoId)", false);
    $ls = new Livestream($mla_database);
    $ls->setYoutubeVideoId($videoId);
    if ($ls->existsInDatabase($videoId)) {
        echo "😊 Already in database: $title ($videoId)<br>";
        continue;
    }

    $ls->setTitle($title);
    $ls->setDescription($description);
    $ls->setPublishedAt($publishedAt);

    if ($ls->saveToDatabase()) {
        echo "✅ Saved: $title ($videoId)<br>";
    } else {
        echo "❌ Failed to save: $title ($videoId)<br>";
    }
}


