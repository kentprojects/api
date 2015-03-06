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

PIPE=/var/www/notifications-pipe
SCRIPT=/vagrant/notifications.php

echo "$PIPE\n$SCRIPT"

if [ ! -p "$PIPE" ]; then
    sudo rm -f "$PIPE"
    sudo mkfifo "$PIPE"
    sudo chown www-data:www-data "$PIPE"
fi

exec 3<>"$PIPE"

while read OPTIONS; do
	sudo -u www-data php "$SCRIPT" $OPTIONS
done <&3