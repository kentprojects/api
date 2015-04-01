# KentProjects &raquo; Admin

This was going to be an Administration area for KentProjects.

It's within the same repository because the Admin area would require direct access to the [Models](../Models), but it
would be accessed under a different sub-domain (`admin.kentprojects.com`).

James didn't want to deal with native HTML pages, so instead wrote a [HtmlElement](./classes/htmlelement.php) class
which allows total modelling of HTML elements, and therefore could model forms and such super quick, with additional
methods for automatic validation of data and such.

However, time constraints mean the Admin area hasn't been finished.

The code remains here for demonstration purposes.

----

## Adding a LESS file watcher in PhpStorm

The Admin area's stylesheets are written in [Less](http://lesscss.org) and converted to `CSS` using a
[command line tool](http://lesscss.org/usage/#command-line-usage). Below are instructions to get
[PhpStorm](https://www.jetbrains.com/phpstorm) to automatically convert the `.less` files to `.css` files whenever a
`.less` file is saved.

- Preferences > Tools > File Watcher
- Add new LESS file watcher

```yml
name: LESS
description: Compiles .less files into .css files

program: /usr/local/bin/nodejs
arguments: /usr/local/bin/lessc --no-color $ProjectFileDir$/admin/assets/less/style.less
output: $ProjectFileDir$/admin/assets/css/style.css
```