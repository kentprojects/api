```
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
```

These are a couple of scripts to assist with common tasks undertaken whilst developing functionality and features for
KentProjects.

To run any of these scripts, consult the [`kentprojects.sh`](../../kentprojects.sh) script.

---

## Deploy

`deploy.sh` will deploy the repository from `develop` to `master`. This is used in the deployment routine. No code is
"built" or "modified" during deployment, since all production-based material (configuration files, keys, etc) all exist
on the production servers. After the final `push` is complete, the server will automatically check out the codebase and
update the `dev` & `live` environments.

## Functions

`functions.sh` contains a simple collection of colours that can be used to make the terminal interaction nicer. It also
contains a clever function to ask users a `[y/N]` style question and quit if the response is no.

## Hotfix

`hotfix.sh` handles all hotfix branches that are required when fixing an urgent bug in the `master` branch. When in
`master`, it will create a hotfix based on the naming pattern `YYYY-MM-DD-SLUG`, where `SLUG` is a one / two word entry
(that the script asks for). When you're finished, running this script again will clone the hotfix branch back into
`master` and `develop`, and destroy the branch.

---

These scripts were designed to be used on the command line, and not in an IDE (such as PhpStorm etc).