# icalsync

This is a simple webapp to synchronize iCalendar files from a URL to a CalDAV server. It is written in PHP and doesn't have any special dependency.

## How to install

1. Clone this repository
2. Make sure icalsync has write access to its own directory

## Configuration

Copy the `config.ini.sample` file as `config.ini` and edit the values:

 - `davpath`: path to the directory used by your CalDAV server (must be writable by icalsync)
 - `davurl`: public URL of your CalDAV server - %u and %c are replace by the username and calendar name respectively
 - `lang`: UI language (check the `lang` folder for available languages)
 - `auth`: authentication method

### Authentication methods

 - `none`: no authentication is required, users creation is available to everyone
 - `http`: basic HTTP authentication (requires a properly configured `.htaccess` file for Apache)

The HTTP method allows authentication against any method supported by Apache (LDAP,...). You can use this to have the same authentication support as your CalDAV server.

## Auto-update

If you use systemd, you can user the provided units (`icalsync.service` and `icalsync.timer`) to automatically update the calendars. Don't forget to edit `icalsync.service` with the correct icalsync installation path.
