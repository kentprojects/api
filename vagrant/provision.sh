#!/usr/bin/env bash
#
# @author: James Dryden <james.dryden@kentprojects.com>
# @license: Copyright KentProjects
# @link: http://kentprojects.com
#

locale-gen en_GB.UTF-8

apt-get update
debconf-set-selections <<< "mysql-server mysql-server/root_password password password"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password password"

apt-get install -y mysql-server apache2 curl screen &&
apt-get install -y php5 php5-cli php5-curl php5-mysqlnd php5-json

if [ "$?" != "0" ]; then
	echo "Something went wrong trying to install the packages. Aborting."
	exit 1
fi

apt-get autoremove -y

ln -s /vagrant /srv/kentprojects

mysql -u root -ppassword < /srv/kentprojects/vagrant/database.setup.sql
php /srv/kentprojects/database/update.php
mysql -u root -ppassword < /srv/kentprojects/vagrant/database.sample.sql

rm /etc/apache2/sites-enabled/*
ln -s /srv/kentprojects/vagrant/apache.conf /etc/apache2/sites-enabled/10-KentProjects.conf
service apache2 restart