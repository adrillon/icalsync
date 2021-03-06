#!/usr/bin/env php
<?php

function update() {
    global $config;

    $inifiles = scandir('users');
    $davpath = $config['davpath'];
    foreach ($inifiles as $ini) {
        if (pathinfo('users/' . $ini, PATHINFO_EXTENSION) != 'ini') {
            continue;
        }

        $username = pathinfo('users/' . $ini, PATHINFO_FILENAME);
        $userpath = $davpath . '/' . $username;

        if (! is_dir($userpath)) {
            mkdir($userpath);
        }

        $calendars = parse_ini_file('users/' . $ini, true);

        foreach ($calendars as $cal) {
            $props = array(
                "tag" => "VCALENDAR",
                "ICAL:calendar-color" => $cal['color'],
                "D:displayname" => $cal['display_name'],
            );

            $opts = array('http' => array('header' => 'User-Agent: Mozilla/5.0'));
            $caldata = file_get_contents($cal['url'], false, stream_context_create($opts));
            $http_code = explode(' ', trim($http_response_header[0]))[1];
            if ($http_code == "200" || $http_code == "302") {
                file_put_contents($userpath . '/' . $cal['name'] . '.props', json_encode($props));
                file_put_contents($userpath . '/' . $cal['name'], $caldata);
            }
        }
    }
}

if (! is_file('config.ini')) {
    die('No configuration found.');
}

$config = parse_ini_file('config.ini');

if (! is_dir('users')) {
    die('Nothing to update.');
}

update();

if (isset($_GET['oneshot'])) {
    Header('Location: index.php');
}
