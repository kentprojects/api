#
# @author: James Dryden <james.dryden@kentprojects.com>
# @license: Copyright KentProjects
# @link: http://kentprojects.com
#
# -*- mode: ruby -*-
# vi: set ft=ruby :
Vagrant.configure("2") do |config|
	config.vm.box = "ubuntu/trusty64"
	config.vm.hostname = "kentprojects"
	config.vm.network "forwarded_port", guest: 80, host: 8080
	config.vm.provision "shell", path: "vagrant/provision.sh"
	config.vm.provider :virtualbox do |vb|
		vb.name = "kentprojects-dev"
	end
end