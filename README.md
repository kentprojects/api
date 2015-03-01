# KentProjects API

The main KentProjects API, used to serve up content to the rest of the application.

----

## Adding a LESS file watcher in PhpStorm

- Preferences > Tools > File Watcher
- Add new LESS file watcher

```yml
name: LESS
description: Compiles .less files into .css files

program: /usr/local/bin/nodejs
arguments: /usr/local/bin/lessc --no-color $ProjectFileDir$/admin/assets/less/style.less
output: $ProjectFileDir$/admin/assets/css/style.css
```