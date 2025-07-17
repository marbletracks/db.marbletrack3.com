<?php
// File: admin/poll/youtube_livestreams.php
// consider using two classes:
// a static data class and then a repository class for database operations
// https://chatgpt.com/g/g-6846e912716c8191892a98da6d093dec-marble-track-3-support/c/684ed9ae-368c-8003-a094-a18c5a9b8f41

/**

    ### Template Suggestion Placeholder

    The `TODO` note suggests a desire for a proper HTML wrapper.
    When ready, consider scaffolding a basic admin-style layout with:

    * `<header>` for branding or nav toggle
    * `<main>` for script output
    * Optional `<aside>` or nav for quick links back to other admin tools

    This can scale well as more admin scripts or logs are added.

*/

declare(strict_types=1);
use Database\LivestreamsRepository;

# Must include here because DH runs FastCGI https://www.phind.com/search?cache=zfj8o8igbqvaj8cm91wp1b7k
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";
if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
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

use Database\LivestreamFactory;
use Database\EpisodeRepository;

function fetchYouTube($url)
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
$need_duration_for_external_ids = []; // will be used to fetch durations for livestreams that are already in the database
// change to true when we need to get all durations and thumbnails
$requirePagination = false; // continue pagination until we find a video already in the database

$pageToken = null;
$count = 0;
$livestreams_repo = new LivestreamsRepository(db: $mla_database);
$episodes_repo = new EpisodeRepository(db: $mla_database);
$results = [];   // will be sent to the template

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

    $response = fetchYouTube($url);

    if (empty($response)) {
        die("No response from YouTube API or empty response.");
    }
    $data = json_decode($response, true);

    foreach ($data['items'] as $item) {
        // print_rob(object: $item, exit: false);
        $ls = LivestreamFactory::fromApiItem(apiItem: $item, db: $mla_database, platform: 'youtube');
        if (!$ls->existsInDatabase(external_id: $ls->getExternalId())) {
            $ls->saveToDatabase();
            $results[] = [
                'livestream_id' => $ls->getLivestreamId(),
                'title' => $ls->getTitle(),
                'status' => '✅ Saved to database',
                'url' => $ls->getWatchUrl(),
                'thumbnail_url' => $ls->getThumbnailUrl(),
                'duration' => $item['duration'],
                'has_episode' => false,
            ];
            $requirePagination = false;
        } else {
            $requirePagination = false;
            $local_livestream = $livestreams_repo->findByExternalId($ls->getExternalId());
            $episode = $episodes_repo->findByLivestreamId($local_livestream->livestream_id);

            $result = [
                'livestream_id' => $local_livestream->livestream_id,
                'title' => $ls->getTitle(),
                'status' => '😊 Already in database',
                'url' => $ls->getWatchUrl(),
                'thumbnail_url' => 'https://i.ytimg.com/vi/' . $item['id']['videoId'] . '/mqdefault.jpg',
                'duration' => $item['duration'],
                'has_episode' => false,
            ];

            if ($episode) {
                $result['has_episode'] = true;
                $result['episode_id'] = $episode->episode_id;
                $result['status'] .= ', episode exists';
            }

            if (!$ls->durationSavedInDatabaseBool(external_id: $ls->getExternalId())) {
                $need_duration_for_external_ids[] = $ls->getExternalId();
            } else {
                $result['status'] .= ' including thumbnail';
            }
            $results[] = $result;
            // break;  // YT API returns most recent first, so we can stop if it's already in the database
        }
    }

    if (!isset($data['items'])) {
        die("No livestream data found or API error.");
    }

    if(count($need_duration_for_external_ids) > 0) {
        // We need to fetch the duration for these livestreams
        $need_duration_for_external_ids = array_unique($need_duration_for_external_ids);
        $need_duration_for_external_ids_string = implode(',', $need_duration_for_external_ids);
        $durationUrl = "https://www.googleapis.com/youtube/v3/videos?" . http_build_query([
            'key' => $apiKey,
            'part' => 'contentDetails',
            'id' => $need_duration_for_external_ids_string
        ]);
        $durationResponse = fetchYouTube($durationUrl);
        $durationData = json_decode($durationResponse, true);

        if (empty($durationData['items'])) {
            die("No duration data found or API error.");
        }
        // print_rob(object: "Fetched duration for " . count($durationData['items']) . " livestreams", exit: false);
        // print_rob($durationData, exit: false);
        $lr = new LivestreamsRepository(db: $mla_database);
        foreach ($durationData['items'] as $item) {
            $ls = $lr->findByExternalId(external_id: $item['id']);
            if (!$ls) {
                print_rob(object: "Livestream with ID {$item['id']} not found in database", exit: false);
                continue;
            }
            $duration = $item['contentDetails']['duration'] ?? null;
            if (!$duration) {
                print_rob(object: "No duration found for livestream ID {$item['id']}", exit: false);
                continue;
            }
            $ls->duration = $duration;
            $lr->saveDurationToDatabase(livestream: $ls);
        }
    }

    $pageToken = $data['nextPageToken'] ?? null;

} while ($requirePagination && $pageToken);

// print_rob(object: $results, exit: false);

$platform = "YouTube";
$page = new \Template(config: $config);
$page->setTemplate(template_file: "admin/poll/livestream_poll_results.tpl.php");
$page->set(name: "results", value: $results);
$page->set(name: "platform", value: $platform);
$inner = $page->grabTheGoods();

$layout = new \Template(config: $config);
$layout->setTemplate(template_file: "layout/admin_base.tpl.php");
$layout->set(name: "page_title", value: "$platform Livestream Poll");
$layout->set(name: "page_content", value: $inner);
$layout->echoToScreen();

