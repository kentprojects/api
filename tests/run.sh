#!/bin/sh
#
# @author: James Dryden <james.dryden@kentprojects.com>
# @license: Copyright KentProjects
# @link: http://kentprojects.com
#

FAIL=" \033[0;31;49m[==]\033[0m "
GOOD=" \033[0;32;49m[==]\033[0m "
WARN=" \033[0;33;49m[==]\033[0m "
TASK=" \033[0;34;49m[==]\033[0m "
USER=" \033[1;1;49m[==]\033[0m "

OUT_CODE=0

if [ -z "$1" ]; then
	for DIR in `find . -mindepth 1 -type d`; do
		[ "$DIR" == "./base" ] && continue
		[ "$DIR" == "./data" ] && continue

		printf "$GOOD Running tests for $DIR\n"
		OUTPUT=$(php phpunit.phar --bootstrap functions.php --color $DIR)
		CODE=$?

		OUT_CODE=$(($OUT_CODE + $CODE))

		if [ $CODE -eq 0 ]; then
			printf "$GOOD "
		else
			printf "$FAIL "
		fi
		echo $OUTPUT"\n"
	done
else
	php phpunit.phar --bootstrap functions.php --color --verbose $@
	OUT_CODE=$?
fi
exit $OUT_CODE
