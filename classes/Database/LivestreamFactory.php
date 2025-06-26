<?php
namespace Database;

use Database\Database;

class LivestreamFactory
{
    /**
     * @param array $apiItem Raw API response item from YouTube or Twitch
     * @param Database $db Database instance
     * @param string $platform Either 'youtube' or 'twitch'
     * @return \Media\RemoteLivestream
     */
    public static function fromApiItem(array $apiItem, Database $db, string $platform = 'youtube'): \Media\RemoteLivestream
    {
        $ls = new \Media\RemoteLivestream(di_dbase: $db);
        $ls->setPlatform(platform: $platform);

        if ($platform === 'youtube') {
            $ls->setExternalId($apiItem['id']['videoId']);
            $ls->setTitle($apiItem['snippet']['title'] ?? '');
            $ls->setDescription($apiItem['snippet']['description'] ?? '');
            $ls->setThumbnailUrl($apiItem['snippet']['thumbnails']['medium']['url'] ?? '');
            // $ls->setDuration($apiItem['contentDetails']['duration'] ?? null); // must get YT duration separately via YouTube API
            $ls->setPublishedAt(date('Y-m-d H:i:s', strtotime($apiItem['snippet']['publishedAt'])));
        } elseif ($platform === 'twitch') {
            $ls->setExternalId($apiItem['id']); // this will eventually be set as external_id
            $ls->setTitle($apiItem['title'] ?? '');
            $ls->setDescription($apiItem['description'] ?? '');
            $ls->setThumbnailUrl(str_replace('%{width}x%{height}', '320x180', $apiItem['thumbnail_url'] ?? ''));
            $ls->setDuration($apiItem['duration'] ?? null);
            $ls->setPublishedAt(date('Y-m-d H:i:s', strtotime($apiItem['published_at'])));
        } else {
            throw new \InvalidArgumentException("Unknown platform: $platform");
        }

        // In the ALTER'd schema, we assume Livestream class has platform and external_id support
        $ls->setPlatform($platform);

        return $ls;
    }
}
