# KentProjects &raquo; Admin

Yay Admin!

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