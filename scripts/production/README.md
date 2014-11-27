# Setting up a KentProjects production environment

Well. This bit is meant to be fun!

At the moment, you can grab any [Ubuntu 14.04 LTS][ubuntu] server image and run the following command:

```bash
curl -L https://raw.githubusercontent.com/kentprojects/api/develop/vagrant/production/setup.sh | sh
```

This will run the [setup script](./setup.sh) and configure a `kentprojects` user for you and your team to use.

Place any public keys in the [`keys.txt` file](./keys.txt) so you'll be able to `ssh` into the server as the
`kentprojects` user!

[ubuntu]: http://www.ubuntu.com/download/server