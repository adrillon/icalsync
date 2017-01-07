<?php

function save_to_ini($username, $calendars) {
    $inilines = array();
    foreach ($calendars as $cal) {
        array_push($inilines, '[' . $cal['name'] . ']');
        foreach ($cal as $key => $val) {
            array_push($inilines, $key . '="' . $val . '"');
        }
        array_push($inilines, '');
    }

    if (! file_put_contents('users/' . $username . '.ini', implode("\r\n", $inilines))) {
        die('Unable to write calendar data. Check that the users directory is writable.');
    }
}

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

// No authentication
if (! array_key_exists('auth', $config) || $config['auth'] = 'none') {
    // Create a new user if needed
    if (! is_dir('users')) {
        mkdir('users');
    }
    if (array_key_exists('newuser', $_GET) && ! empty($_GET['newuser'])) {
        $currentuser = $_GET['newuser'];
        if (! is_file('users/' . $currentuser . '.ini')) {
            file_put_contents('users/' . $currentuser . '.ini', ' ');
        }
        Header('Location: ?user=' . $currentuser);
    }
    // Load the list of users
    $inifiles = scandir('users');
    $users = array();
    foreach ($inifiles as $ini) {
        if (pathinfo('users/' . $ini, PATHINFO_EXTENSION) == 'ini') {
            array_push($users, pathinfo('users/' . $ini, PATHINFO_FILENAME));
        }
    }

    // Set the current user
    if (isset($_GET['user']) && in_array($_GET['user'], $users)) {
        $currentuser = $_GET['user'];
    } else if (count($users) != 0) {
        $currentuser = $users[0];
    } else {
        $currentuser = null;
    }
} else if ($config['auth'] == 'http') {
    $currentuser = $_SERVER['PHP_AUTH_USER'];
}

// Current user's calendars
if ($currentuser) {
    $calendars = parse_ini_file('users/' . $currentuser . '.ini', true);
    $davurl = str_replace('%u', $currentuser, $config['davurl']);
}

// Parse form data
if (count($_POST) > 0) {
    if (! $currentuser) {
        die('You must choose an username.');
    } else if (! array_key_exists('display_name', $_POST) || empty($_POST['display_name'])) {
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

   save_to_ini($currentuser, $calendars); 
   Header('Location: index.php?user=' . $currentuser);

} else if (array_key_exists('delete', $_GET) && $_GET['delete'] == '1') {
    if (! array_key_exists('user', $_GET)) {
        die('THe user is mandatory.');
    } else if ($_GET['user'] != $currentuser) {
        die('Wrong username.');
    } else if (! array_key_exists('cal', $_GET)) {
        die('THe calendar name is mandatory.');
    } else if (! array_key_exists($_GET['cal'], $calendars)) {
        die('The calendar does not exist.');
    }

    unset($calendars[$_GET['cal']]);
    save_to_ini($currentuser, $calendars);
    Header('Location: index.php?user=' . $currentuser);
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

        <?php if (array_key_exists('auth', $config) && $config['auth'] != 'none') { ?>
        <form method="get" id="userform" >
            <label for="user" > <?php echo $lang['userselect']; ?>:</label>
            <?php if ($currentuser) { ?>
            <select name="user" id="user" >
                <?php
                    foreach ($users as $user) {
                        ?>
                            <option value="<?php echo $user; ?>" <?php if ($user == $currentuser) { echo 'selected="selected"'; } ?> ><?php echo $user; ?></option>
                        <?php
                    }
                ?>
            </select>
            <?php } ?>
            <input type="text" name="newuser" placeholder="<?php echo $lang['newuser']; ?>" />
            <input type="submit" value="<?php echo $lang['changeuser']; ?>" />
        </form>
        <?php } ?>

        <?php if ($currentuser) { ?>
        <div id="table" >
            <?php foreach ($calendars as $cal) { ?>
                <form class="tr" method="post" action="?user=<?php echo $currentuser; ?>&cal=<?php echo $cal['name']; ?>" >
                    <span class="td" ><input type="text" name="color" style="color: <?php echo $cal['color']; ?>" value="<?php echo $cal['color']; ?>" /></span>
                    <span class="td" ><input type="text" name="display_name" value="<?php echo $cal['display_name']; ?>" /></span>
                    <span class="td td-remoteurl" ><input type="text" name="url" value="<?php echo $cal['url']; ?>" /></span>
                    <span class="td" ><a href="<?php echo str_replace('%c', $cal['name'], $davurl); ?>" title="<?php echo $lang['local_url']; ?>" ><?php echo $lang['local_url']; ?></a></span>
                    <span class="td" ><input type="submit" class="savebutton" value="<?php echo $lang['save']; ?>" /> - <a class="deletebutton" href="?user=<?php echo $currentuser; ?>&delete=1&cal=<?php echo $cal['name']; ?>" ><?php echo $lang['delete']; ?></a></span>
                </form>
            <?php } ?>

            <form class="tr" method="post" action="?user=<?php echo $currentuser; ?>" >
                <span class="td" ><input type="text" name="color" value="" placeholder="#000000" /></span>
                <span class="td" ><input type="text" name="display_name" value="" placeholder="<?php echo $lang['displayname']; ?>" /></span>
                <span class="td td-remoteurl" ><input type="text" name="url" value="" placeholder="http://..." /></span>
                <span class="td" ></span>
                <span class="td" ><input type="submit" class="savebutton" value="<?php echo $lang['save']; ?>" /></span>
            </form>
        </div>
        <?php
        }
        ?>
    </body>
</html>
