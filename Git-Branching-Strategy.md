# KentProjects

## Git Branching Strategy

### Feature Branches

- May branch off from: `develop`
- Must merge back into: `develop`
- Branch naming convention: anything except: `master`, `develop`, `release/*` or `hotfix/*`

`--no-ff` flag creates new commit even if merge can use fast-forward. Avoids losing historical info of branch.

	Creating feature branch 
	$ git checkout -b feature/coolNewApiFeature develop
	
	Incorperate finished featured on develop
	$ git checkout develop
	$ git merge --no-ff feature/coolNewApiFeature
	$ git branch -d feature/coolNewApiFeature
	$ git push origin develop

### Release branches

- May branch off from: `develop`
- Must merge back into: `develop` and `master`
- Branch naming convention: `release/*`

Prep for release. Minor bug fixes & prep meta-data (version no. increment, build dates, configuration files, etc.).

To keep the changes made in the release branch, we need to merge those back into `develop`, though.

	Creating a release branch
	$ git checkout -b release/1.2 develop
	$ { any relevant commands to build the release }
	
	Finishing a release branch
	$ git checkout master
	$ git merge --no-ff release/0.2
	$ git checkout develop
	$ git merge --no-ff release/0.2
	$ git branch -d release/0.2

### Hotfix branches

May branch off from: `master`
Must merge back into: `master` and `develop`
Branch naming convention: `hotfix/*`

For resolving critical bugs just after a release.

	Creating hotfix branch
	$ git checkout -b hotfix/0.2.1 master
	$ git commit -m "Fixed severe production problem"
	$ { any relevant commands required to rebuild the release }
	
	Finishing a hotfix branch
	$ git checkout master
	$ git merge --no-ff hotfix/0.2.1
	$ git checkout develop
	$ git merge --no-ff hotfix/0.2.1
	$ git branch -d hotfix/0.2.1

---

For more information see [Git Flow](http://nvie.com/posts/a-successful-git-branching-model).