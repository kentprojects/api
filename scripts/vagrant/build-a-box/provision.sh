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

apt-get install -y curl screen vim zsh &&
apt-get install -y mysql-server apache2 curl screen &&
apt-get install -y php5 php5-cli php5-curl php5-mysqlnd php5-json

if [ "$?" != "0" ]; then
	echo "Something went wrong trying to install the packages. Aborting."
	exit 1
fi

apt-get autoremove -y

chsh -s /bin/zsh vagrant

# Clone Oh-My-Zsh, and get a sensible .zshrc and .vimrc
sudo -u vagrant git clone https://github.com/robbyrussell/oh-my-zsh.git /home/vagrant/.oh-my-zsh
sudo -u vagrant wget https://raw.githubusercontent.com/jdrydn/dotfiles/master/.vimrc -O /home/vagrant/.vimrc
sudo -u vagrant wget https://raw.githubusercontent.com/jdrydn/dotfiles/master/.zshrc -O /home/vagrant/.zshrc