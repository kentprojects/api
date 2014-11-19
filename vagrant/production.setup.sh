#!/bin/bash
sudo true

sudo apt-get update
sudo apt-get install mysql-server
sudo apt-get install -y apache2 git curl screen &&
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