#!/bin/sh
#
# @author: James Dryden <james.dryden@kentprojects.com>
# @license: Copyright KentProjects
# @link: http://kentprojects.com
#
PHPUNIT_PHAR_URL="https://github.com/kentprojects/scripts/raw/master/phpunit/phpunit.phar"
#
which mysql > /dev/null
if [ $? -ne 0 ]; then
    echo "No mysql package detected."
    echo "This would suggest this is being run on a development machine rather than your development environment."
    echo "Maybe you should SSH into your development environment and run this again?"
    exit 1
fi
#
if [ ! -f "phpunit.phar" ]; then
    echo "Missing phpunit.phar"
    which wget > /dev/null
    if [ $? -eq 0 ]; then
        echo "Downloading from the KentProjects Scripts repository:"
        wget "$PHPUNIT_PHAR_URL"
    else
        echo "No wget package detected."
        echo "If you wish to do this yourself, download the PHPUnit phar either from http://phpunit.de"
        echo "Or from our Script repository: $PHPUNIT_PHAR_URL"
        exit 1
    fi
fi
#
php phpunit.phar --bootstrap functions.php --color --verbose ./
exit $?