#!/bin/sh
#
# @author: James Dryden <james.dryden@kentprojects.com>
# @license: Copyright KentProjects
# @link: http://kentprojects.com
#

CURRENT_PATH=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
BASE_PATH=$(dirname $CURRENT_PATH)
OUT_CODE=0

source "$BASE_PATH/scripts/helper/functions.sh"
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

		OUT_CODE=$(($OUT_CODE + $CODE))

		if [ $CODE -eq 0 ]; then
			printf "$GOOD "
		else
			printf "$FAIL "
		fi
		echo $OUTPUT"\n"
	done
fi

popd
exit $OUT_CODE
