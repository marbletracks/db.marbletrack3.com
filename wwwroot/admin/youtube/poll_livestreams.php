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

use Youtube\Livestream;

function fetch_url($url)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);

    // print_rob("Response: " . substr($response, 0, 2000) . "...", false);
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


$allItems = [];
$requirePagination = true; // continue pagination until we find a video already in the database

$pageToken = null;
$count = 0;
do {
    $url = "https://www.googleapis.com/youtube/v3/search?" . http_build_query([
        'key' => $apiKey,
        'channelId' => $channelId,
        'part' => 'snippet',
        'type' => 'video',
        'eventType' => 'completed',
        'maxResults' => 50,
        'order' => 'date',
        'pageToken' => $pageToken
    ]);

    $response = fetch_url($url);

    if (empty($response)) {
        die("No response from YouTube API or empty response.");
    }
    $data = json_decode($response, true);

    foreach ($data['items'] as $item) {
        $title = $item['snippet']['title'];
        $publishedAt = date('Y-m-d H:i:s', strtotime($item['snippet']['publishedAt']));
        $videoId = $item['id']['videoId'];
        $ls = new Livestream($mla_database);
        if ($ls->existsInDatabase($videoId)) {
            $requirePagination = false;
            echo "😊 Already in database: $title ($videoId)<br>";
            break;  // YT API returns most recent first, so we can stop if it's already in the database
        }
        $allItems[] = $item;
    }

    if (!isset($data['items'])) {
        die("No livestream data found or API error.");
    }

    $pageToken = $data['nextPageToken'] ?? null;

    // Optional: short delay to avoid API rate limits
    usleep(250000);

} while ($requirePagination && $pageToken);

usort($allItems, function ($a, $b) {
    // sort by oldest first
    // echo "Comparing {$a['snippet']['publishedAt']} with {$b['snippet']['publishedAt']}<br>";
    return $a['snippet']['publishedAt'] <=> $b['snippet']['publishedAt'];
});


foreach ($allItems as $item) {
    $videoId = $item['id']['videoId'];
    $title = $item['snippet']['title'];
    $description = $item['snippet']['description'];
    $publishedAt = date('Y-m-d H:i:s', strtotime($item['snippet']['publishedAt']));

    $ls = new Livestream($mla_database);
    $ls->setYoutubeVideoId($videoId);
    if ($ls->existsInDatabase($videoId)) {
        echo "😊 Already in database: $title ($videoId) so it should not have been added to allItems wtf.<br>";
        continue;
    } else {
        echo "🆕 New livestream: $title ($videoId)<br>";
    }

    $ls->setTitle($title);
    $ls->setDescription($description);
    $ls->setPublishedAt($publishedAt);

    if ($ls->saveToDatabase()) {
        echo "✅ Saved: $publishedAt $title ($videoId)<br>";
    } else {
        echo "❌ Failed to save: $title ($videoId)<br>";
    }
}

echo "<br>Done processing livestreams<br>";
if (count($allItems) == 0) {
    echo "No new livestreams found.<br>";
} else {
    echo "Total livestreams processed: " . count($allItems) . "<br>";
}
echo "TODO make a template for this file.<br>";
echo "Until then, go to <a href='/admin/index.php'>main menu</a>.<br>";
