#!/bin/sh
# Deferred notifications utility.
#
# How to get this running:
# $ sudo nohup /path/to/notifications.sh >/dev/null &
# ($ sudo disown)?
#
# nohup makes sure it keeps running when you close the terminal
# disown makes the terminal forget it exists
#
if [ -z "$PRODUCTION" ]; then
    PIPE=/var/www/notifications-pipe
    SCRIPT=/var/www/kentprojects-api/notifications.php
elif [ -z "$VAGRANT" ]; then
    PIPE=/var/www/notifications-dev-pipe
    SCRIPT=/vagrant/notifications.php
else
    PIPE=/var/www/notifications-dev-pipe
    SCRIPT=/var/www/kentprojects-api-dev/notifications.php
fi

if [ ! -p "$PIPE" ]; then
	rm -f "$PIPE"
	mkfifo "$PIPE"
fi

exec 3<>"$PIPE"

while read OPTIONS; do
	php "$SCRIPT" $OPTIONS
done <&3