<?php

if (! is_file('config.ini')) {
    die('No configuration found.');
}

$config = parse_ini_file('config.ini');
$davpath = $config['davpath'];

if (! is_dir('users')) {
    die('Nothing to update.');
}

$inifiles = scandir('users');
foreach ($inifiles as $ini) {
    if (pathinfo('users/' . $ini, PATHINFO_EXTENSION) != 'ini') {
        continue;
    }

    $username = pathinfo('users/' . $ini, PATHINFO_FILENAME);

    $calendars = parse_ini_file('users/' . $ini, true);

    foreach ($calendars as $cal) {
        $props = array(
            "tag" => "VCALENDAR",
            "ICAL:calendar-color" => $cal['color'],
            "D:displayname" => $cal['display_name'],
        );

        $caldata = file_get_contents($cal['url']);

        file_put_contents($davpath . '/' . $username . '/' . $cal['name'] . '.props', json_encode($props));
        file_put_contents($davpath . '/' . $username. '/' . $cal['name'], $caldata);
    }
}
