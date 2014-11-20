#!/bin/sh
#
# @author: James Dryden <james.dryden@kentprojects.com>
# @license: Copyright KentProjects
# @link: http://kentprojects.com
#
# This is a simple script to setup a KentProjects Web Server.
# Ideally, this would be run after installing a base image of Ubuntu 14.04.
# In a perfect world, only an SSH server & any monitoring services should be installed.
#

# First, we ensure we can run commands as the root user.
# This script will install packages and whatnot, so it's important to run as root.
sudo true

# Lovely colours to make the script more interactive.
FAIL=" \033[0;31;49m[==]\033[0m "
GOOD=" \033[0;32;49m[==]\033[0m "
WARN=" \033[0;33;49m[==]\033[0m "
TASK=" \033[0;34;49m[==]\033[0m "
USER=" \033[1;1;49m[==]\033[0m "

# If you want the development environments, set this to "true".
INCLUDE_DEV=false

# Update the package repositories and install the relevant packages.
sudo apt-get update
sudo apt-get install -y apache2 curl git screen zsh
sudo apt-get install -y php5 php5-cli php5-curl php5-mysqlnd php5-json

# Create a dedicated user for KentProjects, and add it to the relevant groups.
sudo useradd -c KentProjects -d /home/kentprojects -G www-data,sudo -m -s /bin/zsh kentprojects
echo "kentprojects ALL=(ALL:ALL) NOPASSWD: ALL" | sudo tee --append /etc/sudoers

# Clone Oh-My-Zsh
sudo -u kentprojects git clone https://github.com/robbyrussell/oh-my-zsh.git /home/kentprojects/.oh-my-zsh
# Go grab a sensible .zshrc and .vimrc
sudo -u kentprojects wget https://raw.githubusercontent.com/jdrydn/dotfiles/master/.vimrc -O /home/kentprojects/.vimrc
sudo -u kentprojects wget https://raw.githubusercontent.com/jdrydn/dotfiles/master/.zshrc -O /home/kentprojects/.zshrc

# Create directories for KentProjects.
sudo mkdir /var/www/kentprojects-api /var/www/kentprojects-web
# Make www-data the owner of these files.
sudo chown www-data:www-data /var/www/kentprojects-*
# Clone the API and Web repositories to their respective folders.
sudo -u www-data git clone https://github.com/kentprojects/api.git /var/www/kentprojects-api
sudo -u www-data git clone https://github.com/kentprojects/web.git /var/www/kentprojects-web

if $INCLUDE_DEV; then
	sudo mkdir /var/www/kentprojects-api-dev /var/www/kentprojects-web-dev
	sudo chown www-data:www-data /var/www/kentprojects-*
	sudo -u www-data git clone https://github.com/kentprojects/api.git /var/www/kentprojects-api-dev
	sudo -u www-data git clone https://github.com/kentprojects/web.git /var/www/kentprojects-web-dev
	cd /var/www/kentprojects-api-dev && sudo -u www-data git fetch && sudo -u www-data git checkout develop
	cd /var/www/kentprojects-web-dev && sudo -u www-data git fetch && sudo -u www-data git checkout develop
	cd ~
fi

# Setup the SSH folder and add the relevant keys.
sudo -u kentprojects mkdir /home/kentprojects/.ssh
sudo -u kentprojects chmod 700 /home/kentprojects/.ssh
if $INCLUDE_DEV; then
	sudo -u kentprojects cp /var/www/kentprojects-api-dev/vagrant/production/keys.txt /home/kentprojects/.ssh/authorized_keys
else
	sudo -u kentprojects cp /var/www/kentprojects-api/vagrant/production/keys.txt /home/kentprojects/.ssh/authorized_keys
fi
sudo -u kentprojects chmod 644 /home/kentprojects/.ssh/authorized_keys

# Setup the Apache environment
sudo rm /etc/apache2/sites-enabled/*
sudo ln -s /var/www/kentprojects-api/vagrant/production/apache.conf /etc/apache2/sites-enabled/01-KentProjects-Live.conf
$INCLUDE_DEV && sudo ln -s /var/www/kentprojects-api-dev/vagrant/production/apache.dev.conf /etc/apache2/sites-enabled/02-KentProjects-Dev.conf

sudo service apache2 restart

printf "$GOOD Everything looks good from here!\n"
printf "$TASK Now you need to ensure the correct config.ini is present for the API.\n"