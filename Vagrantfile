#
# @author: James Dryden <james.dryden@kentprojects.com>
# @license: Copyright KentProjects
# @link: http://kentprojects.com
#
# -*- mode: ruby -*-
# vi: set ft=ruby :
Vagrant.configure("2") do |config|
	config.vm.box = "puphpet/ubuntu1404-x64"
	config.vm.hostname = "kentprojects"
	config.vm.network "forwarded_port", guest: 80, host: 8060
	config.vm.network "forwarded_port", guest: 3306, host:8306
	config.vm.network "private_network", ip: "172.16.1.11"
	config.vm.provision "shell", path: "vagrant/provision.sh"
	config.vm.provider :virtualbox do |vb|
		vb.name = "kentprojects-api-dev"
	end
	config.vm.provider "parallels" do |vb|
		vb.name = "kentprojects-api-dev"
    end
end