<?php
namespace Youtube;

class Livestream
{
    protected $livestream_id;
    protected $external_id;
    protected $platform; // e.g., 'youtube', 'twitch'
    protected $title;
    protected $description;
    protected $published_at;
    protected $status = 'not';

    public $errors = [];

    public function __construct(
        private $di_dbase,
    ) {
    }

    public function saveToDatabase(): bool
    {
        $this->errors = [];

        $params = [];
        $types = "";
        $types .= "s";
        $params['external_id'] = $this->external_id;
        $types .= "s";
        $params['platform'] = $this->platform;
        $types .= "s";
        $params['title'] = $this->title;
        $types .= "s";
        $params['description'] = $this->description;
        $types .= "s";
        $params['published_at'] = $this->published_at;
        $types .= "s";
        $params['status'] = $this->status;

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
            return true;
        }
        return false;
    }


    // Setters
    public function setExternalId($val)
    {
        $this->external_id = $val;
    }
    public function setPlatform(string $platform)
    {
        $this->platform = $platform;
    }
    public function setTitle($val)
    {
        $this->title = $val;
    }
    public function setDescription($val)
    {
        $this->description = $val;
    }
    public function setPublishedAt($val)
    {
        $this->published_at = $val;
    }
    public function setStatus($val)
    {
        $this->status = $val;
    }

    // Getters
    public function getExternalId()
    {
        return $this->external_id;
    }
    public function getLivestreamId()
    {
        return $this->livestream_id;
    }
}
