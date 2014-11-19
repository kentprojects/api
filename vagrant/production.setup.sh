#!/bin/bash
sudo true

FAIL=" \033[0;31;49m[==]\033[0m "
GOOD=" \033[0;32;49m[==]\033[0m "
WARN=" \033[0;33;49m[==]\033[0m "
TASK=" \033[0;34;49m[==]\033[0m "
USER=" \033[1;1;49m[==]\033[0m "

printf "$TASK Please enter a MySQL Root Password: "
read MYSQL_PASSWORD

sudo apt-get update
debconf-set-selections <<< "mysql-server mysql-server/root_password password $MYSQL_PASSWORD"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $MYSQL_PASSWORD"
sudo apt-get install -y mysql-server apache2 git curl screen &&
sudo apt-get install -y php5 php5-cli php5-curl php5-mysqlnd php5-json

# If this is becoming a script, change this to use useradd.
sudo adduser --disabled-password --home /home/kentprojects --shell /usr/bin/zsh kentprojects
sudo addgroup kentprojects www-data

sudo -u kentprojects mkdir /home/kentprojects/.ssh
sudo -u kentprojects chmod 700 /home/kentprojects/.ssh
sudo -u kentprojects touch /home/kentprojects/.ssh/authorized_keys

sudo -u kentprojects git clone https://github.com/robbyrussell/oh-my-zsh.git /home/kentprojects/.oh-my-zsh
sudo -u kentprojects git clone https://github.com/jdrydn/dotfiles.git /home/kentprojects/.dotfiles
sudo -u kentprojects cp /home/kentprojects/.dotfiles/.zshrc /home/kentprojects/.zshrc
sudo -u kentprojects cp /home/kentprojects/.dotfiles/.vimrc /home/kentprojects/.vimrc

sudo -u www-data git clone https://github.com/kentprojects/api.git /var/www/kentprojects-api
sudo -u www-data git clone https://github.com/kentprojects/web.git /var/www/kentprojects-web

cat /var/www/kentprojects-api/vagrant/production.key.*.txt > /home/kentprojects/.ssh/authorized_keys