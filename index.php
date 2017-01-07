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
if (isset($_POST['user']) && in_array($_POST['user'], $users)) {
    $currentuser = $_POST['user'];
}

?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $lang['title']; ?></title>
        <meta charset="UTF-8" />
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
    </body>
</html>
