<?php

// consider using two classes:
// a static data class and then a repository class for database operations
// https://chatgpt.com/g/g-6846e912716c8191892a98da6d093dec-marble-track-3-support/c/684ed9ae-368c-8003-a094-a18c5a9b8f41


/**
    ### Optionally Extract a `LivestreamFactory` or Data Wrapper

    Consider isolating the transformation of `$item` into a
    `Livestream` object into a small helper or class, such as:

    `$ls = LivestreamFactory::fromApiItem($item);`

    This improves modularity and makes testing or
    extending the behavior easier.


    ### Template Suggestion Placeholder

    The `TODO` note suggests a desire for a proper HTML wrapper.
    When ready, consider scaffolding a basic admin-style layout with:

    * `<header>` for branding or nav toggle
    * `<main>` for script output
    * Optional `<aside>` or nav for quick links back to other admin tools

    This can scale well as more admin scripts or logs are added.

*/

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

    if (curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        print_rob(object: "Response: " . substr($response, 0, 2000) . "...", exit: false);
        print_rob("https://console.cloud.google.com/apis/credentials/key/59d4845a-0851-48f6-82a4-4d33b75ebcf1?inv=1&invt=Ab011A&project=db-mt3-com-15-june-2025");
        throw new Exception("Non-200 response (see above)");
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
            echo "😊 Already in database: $title (<a href=https://www.youtube.com/watch?v=$videoId>$videoId</a>)<br>";
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
        continue;
    } else {
        echo "🆕 New livestream: $title (<a href=https://www.youtube.com/watch?v=$videoId>$videoId</a>)<br>";
    }

    $ls->setTitle($title);
    $ls->setDescription($description);
    $ls->setPublishedAt($publishedAt);

    if ($ls->saveToDatabase()) {
        echo "✅ Saved: $publishedAt $title ($videoId)<br>";
        // Link to a page to create an episode for this livestream:
        // blank target because might be more than one new livestream in this list
        echo "<a class='btn' target='_blank' href='/admin/episodes/create.php?livestream_id={$ls->getLivestreamId()}'>🎥 Create Episode</a><br>";
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
