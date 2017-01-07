<?php

// Load the configuration
if (! is_file('config.ini')) {
    die('No configuration found.');
}
$config = parse_ini_file('config.ini');

// Load the language file
$langfile = 'lang/' . $config['lang'] . '.php';
if (! is_file($langfile)) {
    die('Language file not found');
}
require_once($langfile);

// Load the list of users
if (! is_dir('users')) {
    mkdir('users');
}
$inifiles = scandir('users');
$users = array();
foreach ($inifiles as $ini) {
    if (pathinfo('users/' . $ini, PATHINFO_EXTENSION) == 'ini') {
        array_push($users, pathinfo('users/' . $ini, PATHINFO_FILENAME));
    }
}
if (count($users) == 0) {
    die('No users found.');
}

// Set the current user
$currentuser = $users[0];
if (isset($_GET['user']) && in_array($_GET['user'], $users)) {
    $currentuser = $_GET['user'];
}

// Current user's calendars
$calendars = parse_ini_file('users/' . $currentuser . '.ini', true);
$davurl = str_replace('%u', $currentuser, $config['davurl']);

// Parse form data
if (count($_POST) > 0) {
    if (! array_key_exists('display_name', $_POST) || empty($_POST['display_name'])) {
        die('The display name is mandatory.');
    } else if (! array_key_exists('url', $_POST) ||  empty($_POST['url'])) {
        die('The remote URL is mandatory.');
    }

    if (isset($_GET['cal']) && array_key_exists($_GET['cal'], $calendars)) {
        $calendars[$_GET['cal']] = array_merge($calendars[$_GET['cal']], $_POST);
    } else {
        $calname = md5($_POST['url'] . microtime());
        $calendars[$calname] = $_POST;
        $calendars[$calname]['name'] = $calname;
    }

    $inilines = array();
    foreach ($calendars as $cal) {
        array_push($inilines, '[' . $cal['name'] . ']');
        foreach ($cal as $key => $val) {
            array_push($inilines, $key . '="' . $val . '"');
        }
        array_push($inilines, '');
    }

    if (! file_put_contents('users/' . $currentuser . '.ini', implode("\r\n", $inilines))) {
        die('Unable to write calendar data. Check that the users directory is writable.');
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $lang['title']; ?></title>
        <meta charset="UTF-8" />
        <link rel="stylesheet" type="text/css" href="style.css" />
    </head>
    <body>
        <h1><?php echo $lang['title']; ?></h1>

        <form method="get" >
            <label for="user" > <?php echo $lang['userselect']; ?>:</label>
            <select name="user" id="user" >
                <?php
                    foreach ($users as $user) {
                        ?>
                            <option value="<?php echo $user; ?>" <?php if ($user == $currentuser) { echo 'selected="selected"'; } ?> ><?php echo $user; ?></option>
                        <?php
                    }
                ?>
            </select>
            <input type="submit" value="<?php echo $lang['changeuser']; ?>" />
        </form>

        <div id="table" >
            <div class="tr" id="thead" >
                <span class="td" ><?php echo $lang['color']; ?></span>
                <span class="td" ><?php echo $lang['displayname']; ?></span>
                <span class="td" ><?php echo $lang['local_url']; ?></span>
                <span class="td" ><?php echo $lang['remote_url']; ?></span>
                <span class="td" ><?php echo $lang['operations']; ?></span>
            </div>

            <?php
            foreach ($calendars as $cal) {
            ?>
                <form class="tr" method="post" action="?user=<?php echo $currentuser; ?>&cal=<?php echo $cal['name']; ?>" >
                    <span class="td" ><input type="text" name="color" value="<?php echo $cal['color']; ?>" /></span>
                    <span class="td" ><input type="text" name="display_name" value="<?php echo $cal['display_name']; ?>" /></span>
                    <span class="td" ><a href="<?php echo str_replace('%c', $cal['name'], $davurl); ?>" title="<?php echo $lang['local_url']; ?>" ><?php echo $lang['local_url']; ?></a></span>
                    <span class="td" ><input type="text" name="url" value="<?php echo $cal['url']; ?>" /></span>
                    <span class="td" ><input type="submit" value="<?php echo $lang['save']; ?>" /></a></span>
                </form>
            <?php
            }
            ?>

            <form class="tr" method="post" action="?user=<?php echo $currentuser; ?>" >
                <span class="td" ><input type="text" name="color" value="" placeholder="#000000" /></span>
                <span class="td" ><input type="text" name="display_name" value="" /></span>
                <span class="td" ></span>
                <span class="td" ><input type="text" name="url" value="" placeholder="http://..." /></span>
                <span class="td" ><input type="submit" value="<?php echo $lang['save']; ?>" /></span>
            </form>
        </div>
    </body>
</html>
