#!/bin/sh
#
# @author: James Dryden <james.dryden@kentprojects.com>
# @license: Copyright KentProjects
# @link: http://kentprojects.com
#
ssh kentprojects@kentprojects.com <<'ENDSSH'
cd /var/www/kentprojects-api-dev && sudo -u www-data git pull && \
php database/update.php && \
sudo service apache2 restart && \
sudo service memcached restart
ENDSSH