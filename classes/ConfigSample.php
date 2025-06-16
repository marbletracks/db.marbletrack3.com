<?php

class Config {

    public $domain_name = 'db.marbletrack3.com';  // used for cookies
    public $cookie_name = 'mt3login'; // used for cookies
    public $cookie_lifetime = 60 * 60 * 24 * 30; // 30 days
    public $app_path = '/home/barefoot_rob/db.marbletrack3.com';

    public $dbHost = "localhost";
    public $dbUser = "";
    public $dbPass = "";
    public $dbName = "";
    // https://console.cloud.google.com/apis/credentials
    public $youtube_key = ""; // YouTube API key loads livestream meta data
    public $mt3_channel_id = ""; // https://www.youtube.com/account_advanced
}