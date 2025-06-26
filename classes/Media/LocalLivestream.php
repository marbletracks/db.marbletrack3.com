<?php
namespace Media;

final class LocalLivestream
{
    public function __construct(
        public int $livestream_id,
        public string $external_id,
        public string $platform,
        public string $title,
        public string $description,
        public ?string $thumbnail_url = null,
        public ?string $duration = null,
        public ?string $published_at,
        public string $status,
        public string $created_at
    ) {
        if(!in_array($this->platform, ['youtube', 'twitch'], true)) {
            throw new \InvalidArgumentException("Invalid platform: $this->platform");
        }
        switch ($this->platform) {
            case 'youtube':
                $this->watch_url = "https://www.youtube.com/watch?v={$this->external_id}";
                $this->thumbnail_url = "https://i.ytimg.com/vi/{$this->external_id}/mqdefault.jpg";
                $this->duration = null; // YouTube livestreams do not have a fixed duration
                break;
            case 'twitch':
                $this->watch_url = "https://www.twitch.tv/videos/{$this->external_id}";
                $this->thumbnail_url = "https://static-cdn.jtvnw.net/previews-ttv/live_user_{$this->external_id}-320x180.jpg";
                $this->duration = null; // Twitch livestreams do not have a fixed duration
                break;
        }
    }
}
