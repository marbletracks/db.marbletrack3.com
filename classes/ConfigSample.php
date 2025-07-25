<?php

class Config {

    public $domain_name = 'db.marbletrack3.com';  // used for cookies
    public $cookie_name = 'mt3login'; // used for cookies
    public $cookie_lifetime = 60 * 60 * 24 * 30; // 30 days
    public $app_path = '/home/barefoot_rob/db.marbletrack3.com';

    // for testing, use $dbHost, $dbUser, $dbPass, $testDbName

    public $dbHost = "";
    public $dbName = "";
    public $testDbName = "dbmt3_test";
    public $dbUser = "";
    public $dbPass = "";
    // https://console.cloud.google.com/apis/credentials
    public $youtube_key = ""; // YouTube API key loads livestream meta data
    public $mt3_channel_id = ""; // https://www.youtube.com/account_advanced


    public $twitch_client_id = ""; // https://dev.twitch.tv/console/apps
    public $twitch_client_secret = "";
    public $twitch_user_name = "marble_track_construction"; // twitch_livestreams.php will use this to determine the channel ID
}