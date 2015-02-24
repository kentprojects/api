#!/usr/bin/env sh
#
# @author: James Dryden <james.dryden@kentprojects.com>
# @license: Copyright KentProjects
# @link: http://kentprojects.com
#
# Force the locale to en_GB. Because sanity.
locale-gen en_GB.UTF-8
#
# Update the local apt-get repository information.
apt-get update
# Set the default mysql-server root password.
echo "mysql-server mysql-server/root_password password password" | debconf-set-selections
echo "mysql-server mysql-server/root_password_again password password" | debconf-set-selections
# Install MySQL, Apache, Curl, Screen and PHP.
apt-get install -y mysql-server apache2 curl screen && \
apt-get install -y php5 php5-cli php5-curl php5-mysqlnd php5-json
# If one of them failed, then abort.
if [ $? -ne 0 ]; then
	echo "Something went wrong trying to install the packages. ABORTING."
	exit 1
fi
#
# Remove an unnecessary packages.
apt-get autoremove -y
#
# Build the development database.
mysql -u root -ppassword < /vagrant/vagrant/database.sql
#   And it's structure.
php /vagrant/database/update.php
#   And then import some sample data.
mysql -u root -ppassword kentprojects < /vagrant/database/Sample.sql
#
# Clear out the original Apache virtualhosts.
rm /etc/apache2/sites-enabled/*
# Link the KentProjects virtualhost.
ln -s /vagrant/vagrant/apache.conf /etc/apache2/sites-enabled/10-KentProjects.conf
# And restart Apache.
service apache2 restart
#
# Make a folder for the Log class to store it's logs.
mkdir -p /var/www/logs
# And ensure that the web can write to it.
chown www-data:www-data /var/www/logs
#
# Add `api.dev.kentprojects.com` to the hosts file.
echo "127.0.0.1 api.kentprojects.local" >> /etc/hosts