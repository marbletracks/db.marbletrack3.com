<?php

class Config {

    /** @var string */
    public $domain_name = 'db.marbletrack3.com';  // used for cookies
    /** @var string */
    public $cookie_name = 'mt3login'; // used for cookies
    /** @var int */
    public $cookie_lifetime = 60 * 60 * 24 * 30; // 30 days
    /** @var string */
    public $app_path = '/home/barefoot_rob/db.marbletrack3.com';

    // for testing, use $dbHost, $dbUser, $dbPass, $testDbName

    /** @var string */
    public $dbHost = "";
    /** @var string */
    public $dbName = "";
    /** @var string */
    public $testDbName = "dbmt3_test";
    /** @var string */
    public $dbUser = "";
    /** @var string */
    public $dbPass = "";
    // https://console.cloud.google.com/apis/credentials
    /** @var string */
    public $youtube_key = ""; // YouTube API key loads livestream meta data
    /** @var string */
    public $mt3_channel_id = ""; // https://www.youtube.com/account_advanced


    /** @var string */
    public $twitch_client_id = ""; // https://dev.twitch.tv/console/apps
    /** @var string */
    public $twitch_client_secret = "";
    /** @var string */
    public $twitch_user_name = "marble_track_construction"; // twitch_livestreams.php will use this to determine the channel ID
}