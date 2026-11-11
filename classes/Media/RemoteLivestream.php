<?php
namespace Media;

class RemoteLivestream
{
    /** @var int|string|null */
    protected $livestream_id;
    /** @var string|null */
    protected $external_id;
    /** @var string|null */
    protected $platform; // e.g., 'youtube', 'twitch'
    /** @var string|null */
    protected $watch_url;
    /** @var string|null */
    protected $title;
    /** @var string|null */
    protected $description;
    /** @var string|null */
    protected $thumbnail_url;
    /** @var string|null */
    protected $duration; // Duration can be null for livestreams
    /** @var string|null */
    protected $published_at;
    /** @var string */
    protected $status = 'not';

    /** @var string[] */
    public $errors = [];

    public function __construct(
        private \Database\DbInterface $di_dbase,
    ) {
    }

    public function saveToDatabase(): bool
    {
        $this->errors = [];

        $params = [];
        $types = "";
        $params['external_id'] = $this->external_id;
        $params['platform'] = $this->platform;
        $params['title'] = $this->title;
        $params['description'] = $this->description;
        $params['thumbnail_url'] = $this->thumbnail_url;
        $params['duration'] = $this->duration;
        $params['published_at'] = $this->published_at;
        $params['status'] = $this->status;
        // Make types be s for each param.
        // If future Rob inserts a non-string value above
        // see the commit after 4576b0408226c43e610f4b4b2cfc8fce2ace291e
        // You might be able to just revert this commit.
        $types = str_repeat(string: "s", times: count(value: $params));

        if (empty($this->livestream_id)) {
            $this->livestream_id = $this->di_dbase->insertFromRecord("`livestreams`", $types, $params);
        } else {
            $this->di_dbase->updateFromRecord("`livestreams`", $types, $params, "`livestream_id` = " . intval($this->livestream_id));
        }
        return true;  // Maybe add a Transaction and try-catch here?
    }

    public function existsInDatabase(string $external_id): bool {
        // TODO fix rare bug where Twitch and YouTube have the same external_id
        if (empty($external_id)) {
            echo "No external_id provided to check in database<br>";
            return false;
        }
        $query = "SELECT `livestream_id` FROM `livestreams` WHERE `external_id` = ?";
        $result = $this->di_dbase->fetchResults($query, 's', $external_id);

        if ($result->toArray()) {
            $this->livestream_id = $result->toArray()[0]['livestream_id'];
            return true;
        }
        return false;
    }

    public function durationSavedInDatabaseBool(string $external_id): bool
    {
        if (empty($external_id)) {
            echo "No external_id provided to check in database<br>";
            return false;
        }
        $query = "SELECT `livestream_id` FROM `livestreams` WHERE `external_id` = ? AND `duration` IS NOT NULL";
        $result = $this->di_dbase->fetchResults($query, 's', $external_id);

        if ($result->toArray()) {
            return true;
        }
        return false;
    }

    // Setters
    /** @param string|null $val as the platform's API gave it */
    public function setExternalId($val): void
    {
        $this->external_id = $val;
        $this->determineWatchUrl();  // Set watch_url based on platform and external_id
    }
    public function setPlatform(string $platform): void
    {
        $this->platform = $platform;
    }
    private function determineWatchUrl(): void
    {
        if ($this->platform === 'youtube') {
            $this->watch_url = "https://www.youtube.com/watch?v={$this->external_id}";
        } elseif ($this->platform === 'twitch') {
            $this->watch_url = "https://www.twitch.tv/videos/{$this->external_id}";
        } else {
            throw new \InvalidArgumentException("Unknown platform: {$this->platform}");
        }
    }
    /** @param string|null $val as the platform's API gave it */
    public function setTitle($val): void
    {
        $this->title = $val;
    }
    /** @param string|null $val as the platform's API gave it */
    public function setDescription($val): void
    {
        $this->description = $val;
    }
    /** @param string|null $val as the platform's API gave it */
    public function setThumbnailUrl($val): void
    {
        $this->thumbnail_url = $val;
    }
    /** @param string|null $val as the platform's API gave it */
    public function setDuration($val): void
    {
        $this->duration = $val;
    }
    /** @param string|null $val as the platform's API gave it */
    public function setPublishedAt($val): void
    {
        $this->published_at = $val;
    }
    /** @param string|null $val as the platform's API gave it */
    public function setStatus($val): void
    {
        $this->status = $val;
    }

    // Getters
    /**
     * @return string|null
     */
    public function getExternalId()
    {
        return $this->external_id;
    }
    /**
     * @return int|string|null
     */
    public function getLivestreamId()
    {
        return $this->livestream_id;
    }
    public function getWatchUrl(): string
    {
        return $this->watch_url;
    }
    public function getThumbnailUrl(): string
    {
        return $this->thumbnail_url;
    }
    public function getTitle(): string
    {
        return $this->title;
    }
}
