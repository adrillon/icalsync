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
 - `sleep_time`: how long should the updater wait between each calendar update (in seconds)

## Auto-update

If you use systemd, you can copy `icalsync.service` to `/etc/systemd/system` and enable the `icalsync` service to have your calendars update automatically. Don't forget to edit `icalsync.service` with the correct icalsync installation path.
