#!/usr/bin/env php
<?php

if (! is_file('config.ini')) {
    die('No configuration found.');
}

$config = parse_ini_file('config.ini');
$davpath = $config['davpath'];

if (! is_dir('users')) {
    die('Nothing to update.');
}

$sleeptime = 60;
if (array_key_exists('sleep_time', $config)) {
    $sleeptime = $config['sleep_time'];
}

while (true) {
    $inifiles = scandir('users');
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

            $caldata = file_get_contents($cal['url']);

            file_put_contents($userpath . '/' . $cal['name'] . '.props', json_encode($props));
            file_put_contents($userpath . '/' . $cal['name'], $caldata);
        }
    }

    sleep($sleeptime);
}
