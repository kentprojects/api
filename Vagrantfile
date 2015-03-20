#
# @author: James Dryden <james.dryden@kentprojects.com>
# @license: Copyright KentProjects
# @link: http://kentprojects.com
#
# vim: set ft=ruby :
Vagrant.configure("2") do |config|
	config.vm.box = "kentprojects/api"
	config.vm.provider "virtualbox" do |v, override|
		override.vm.box = "ubuntu/trusty64"
		v.name = "kentprojects-api"
	end
	config.vm.provider "parallels" do |v, override|
		override.vm.box = "puphpet/ubuntu1404-x64"
		v.name = "kentprojects-api"
	end
	config.vm.hostname = "kentprojects"
	# A port for the API
	config.vm.network "forwarded_port", guest: 80, host: 8060
	# And a port for the Admin
	config.vm.network "forwarded_port", guest: 81, host: 8061
	config.vm.network "private_network", ip: "172.16.1.11"
	config.vm.provision "shell", path: "vagrant/provision.sh"
end