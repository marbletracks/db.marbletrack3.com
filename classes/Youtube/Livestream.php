<?php
namespace Youtube;

class Livestream
{
    protected $livestream_id;
    protected $youtube_video_id;
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
        $params['youtube_video_id'] = $this->youtube_video_id;
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

    public function existsInDatabase(string $youtube_video_id) {
        $query = "SELECT `livestream_id` FROM `livestreams` WHERE `youtube_video_id` = ?";
        $result = $this->di_dbase->fetchResults($query, 's', $youtube_video_id);

        if ($result->valid()) {
            $row = $result->current();
            $this->livestream_id = $row['livestream_id'];
            return true;
        }
        return false;
    }


    // Setters
    public function setYoutubeVideoId($val)
    {
        $this->youtube_video_id = $val;
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
    public function getYoutubeVideoId()
    {
        return $this->youtube_video_id;
    }
    public function getLivestreamId()
    {
        return $this->livestream_id;
    }
}
