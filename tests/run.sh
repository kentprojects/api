#!/bin/sh
#
# @author: James Dryden <james.dryden@kentprojects.com>
# @license: Copyright KentProjects
# @link: http://kentprojects.com
#
if [ -z "$1" ]; then
	echo "Please specify a folder. For now. It'll do something clever later."
	exit 2
fi
php phpunit.phar --bootstrap functions.php --color --verbose $@
exit $?