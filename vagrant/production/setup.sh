#!/bin/bash
#
# @author: James Dryden <james.dryden@kentprojects.com>
# @license: Copyright KentProjects
# @link: http://kentprojects.com
#
# This is a simple script to setup a KentProjects Web Server
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

# Update the package repositories and install the relevant packages.
sudo apt-get update
sudo apt-get install -y apache2 curl git screen zsh
sudo apt-get install -y php5 php5-cli php5-curl php5-mysqlnd php5-json

# Create a dedicated user for KentProjects, and add it to the relevant groups.
sudo useradd -c KentProjects -d /home/kentprojects -G www-data,sudo -m -s /bin/zsh kentprojects

# Clone Oh-My-Zsh
sudo -u kentprojects git clone https://github.com/robbyrussell/oh-my-zsh.git /home/kentprojects/.oh-my-zsh
# Clone James's dotfiles and grab the .zshrc and .vimrc
sudo -u kentprojects git clone https://github.com/jdrydn/dotfiles.git /home/kentprojects/.dotfiles
sudo -u kentprojects cp /home/kentprojects/.dotfiles/.zshrc /home/kentprojects/.zshrc
sudo -u kentprojects cp /home/kentprojects/.dotfiles/.vimrc /home/kentprojects/.vimrc

# Create directories for KentProjects.
sudo mkdir /var/www/kentprojects-api /var/www/kentprojects-web
# Make www-data the owner of these files.
sudo chown www-data:www-data /var/www/kentprojects-*
# Clone the API and Web repositories to their respective folders.
sudo -u www-data git clone https://github.com/kentprojects/api.git /var/www/kentprojects-api
sudo -u www-data git clone https://github.com/kentprojects/web.git /var/www/kentprojects-web

# Setup the SSH folder and add the relevant keys.
sudo -u kentprojects mkdir /home/kentprojects/.ssh
sudo -u kentprojects chmod 700 /home/kentprojects/.ssh
sudo -u kentprojects touch /home/kentprojects/.ssh/authorized_keys
# cat /var/www/kentprojects-api/vagrant/production.key.*.txt > /home/kentprojects/.ssh/authorized_keys