#!/bin/sh
#
# @author: James Dryden <james.dryden@kentprojects.com>
# @license: Copyright KentProjects
# @link: http://kentprojects.com
#

CURRENT_PATH=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
BASE_PATH=$(dirname $CURRENT_PATH)
OUT_CODE=0

FAIL=" \033[0;31;49m[==]\033[0m "
GOOD=" \033[0;32;49m[==]\033[0m "
WARN=" \033[0;33;49m[==]\033[0m "
TASK=" \033[0;34;49m[==]\033[0m "
USER=" \033[1;1;49m[==]\033[0m "

pushd "$CURRENT_PATH"

#
# A simple function to run the PhpUnit tests.
#
# @param string folder
# @return int
#
function runPhpUnit
{
	php phpunit.phar --bootstrap functions.php --color --verbose $1
	return $?
}

if [ -n "$1" ]; then
	runPhpUnit $1
	OUT_CODE=$?
else
	for DIR in `find . -mindepth 1 -type d`; do
		[ "$DIR" == "./base" ] && continue
		[ "$DIR" == "./data" ] && continue

		printf "$GOOD Running tests for %s\n" ${DIR:2}
		OUTPUT=$(runPhpUnit $DIR)
		CODE=$?

		if [ $CODE -eq 0 ]; then
			printf "$GOOD $OUTPUT\n"
		else
			printf "$FAIL $OUTPUT\n"
		fi

		OUT_CODE=$(($OUT_CODE + $CODE))
	done
fi

popd
exit $OUT_CODE
