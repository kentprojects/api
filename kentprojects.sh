#!/bin/bash

BASE_PATH=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
pushd "$BASE_PATH" > /dev/null

FAIL=" \033[0;31;49m[==]\033[0m "
GOOD=" \033[0;32;49m[==]\033[0m "
WARN=" \033[0;33;49m[==]\033[0m "
TASK=" \033[0;34;49m[==]\033[0m "
USER=" \033[1;1;49m[==]\033[0m "

#
# Internal function for asking questions.
#
# @param string
# @param string
# @return int
#
function Question()
{
	if [ -z "$1" ]; then
		printf "$FAIL \033[0;31;49mQuestion Error\033[0m Please supply a question."
	fi
	if [ -z "$2" ]; then
		printf "$FAIL \033[0;31;49mQuestion Error\033[0m Please supply an error message."
	fi

	printf "$USER $1 [y/N] "
	read CONFIRM
	printf "\n"
	case "$CONFIRM" in
		"y") return 0 ;;
		"Y") return 0 ;;
		*) printf "$FAIL $2\n"; exit 1 ;;
	esac
}

#
# Internal function to deploy the develop branch.
#
# @return void
#
function circleCiDeployDevelop()
{
    ssh kentprojects@kentprojects.com <<'ENDSSH'
cd /var/www/kentprojects-api-dev && sudo -u www-data git pull && \
php database/update.php && \
sudo service apache2 restart && \
sudo service memcached restart
ENDSSH
}
#
# Internal function to deploy the master branch.
#
# @return void
#
function circleCiDeployMaster()
{
    ssh kentprojects@kentprojects.com <<'ENDSSH'
cd /var/www/kentprojects-api && sudo -u www-data git pull && \
php database/update.php && \
sudo service apache2 restart && \
sudo service memcached restart
ENDSSH
}

#
# Internal function for deploying the codebase.
#
# @return void
#
function deploy()
{
	CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
	SOURCE_BRANCH="develop"
	DESTINATION_BRANCH="master"

	if [ "$CURRENT_BRANCH" != "$SOURCE_BRANCH" ]; then
		printf "$FAIL The current working directory at $BASE_PATH isn't at the '$SOURCE_BRANCH' branch.\n"
		exit 2
	fi

	if ! git diff-index --quiet HEAD --; then
		printf "$FAIL The current working directory at $BASE_PATH has some outstanding changes.\n"
		printf "$USER Please handle all outstanding changes before deploying.\n"
		exit 3
	fi

	git push -q
	if [ "$?" != "0" ]; then
		printf "$FAIL There was an error when pushing the repository.\n"
		printf "$USER Please ensure you can push to the remote before continuing.\n"
		exit 4
	fi

	printf "$GOOD Repository looks good!\n\n"
	printf "This deployment script is designed to push the content from one branch to another.\n"
	printf "Usually it's designed to deploy a 'develop' branch to a 'master', quick and simple!\n\n"

	printf "$TASK This script will merge '$DESTINATION_BRANCH' with '$SOURCE_BRANCH' without fast-forwarding.\n"
	printf "$TASK And then push '$DESTINATION_BRANCH' up to the server.\n"

	Question "Do you wish to proceed?" "User declined the deployment."

	git pull &&
	git checkout $DESTINATION_BRANCH &&
	git merge --no-ff $SOURCE_BRANCH -m "Merging develop into master for deployment." &&
	git push origin $DESTINATION_BRANCH &&
	git checkout $SOURCE_BRANCH

	if [ "$?" != "0" ]; then
		SPACE="      "
		printf "$FAIL There was an error whilst deploying the repository :(\n"
		printf "$USER Please check the repository, or run the following by hand:\n"
		printf "$SPACE git pull\n$SPACE git checkout $DESTINATION_BRANCH\n$SPACE git merge --no-ff $SOURCE_BRANCH -m \"Deployment\"\n"
		printf "$SPACE git push origin $DESTINATION_BRANCH\n$SPACE git checkout $SOURCE_BRANCH\n"
		exit 5
	fi
}

#
# Internal function for hotfixing the codebase.
#
# @return void
#
function hotfix()
{
	CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)

	if [ "$CURRENT_BRANCH" != "master" ] && [[ $CURRENT_BRANCH != hotfix* ]]; then
		printf "$FAIL The current working directory at $BASE_PATH isn't at the 'master' branch.\n"
		exit 2
	fi

	if ! git diff-index --quiet HEAD --; then
		printf "$FAIL The current working directory at $BASE_PATH has some outstanding changes.\n"
		printf "$USER Please handle all outstanding changes before hotfixing.\n"
		exit 1
	fi

	printf "$GOOD Repository looks good!\n\n"
	printf "This hotfix script is designed to create a hotfix branch for quick fixes to the master branch.\n"
	printf "The hotfix branch will be merged back into master & develop to ensure changes aren't lost!\n\n"

	if [[ $CURRENT_BRANCH == hotfix* ]]; then
		printf "$TASK This script will merge your hotfix branch into the 'master' and 'develop' branches.\n"
		printf "$TASK This will complete the hotfix and return you to 'master'!\n"
		Question "Do you wish to proceed?" "User declined to close a hotfix."

		git checkout master
		git merge --no-ff $CURRENT_BRANCH -m "Merging hotfix '$CURRENT_BRANCH' with master."
		git checkout develop
		git merge --no-ff $CURRENT_BRANCH -m "Merging hotfix '$CURRENT_BRANCH' with develop."
		git branch -d $CURRENT_BRANCH
		git checkout master
		git push --all origin
	else
		printf "$TASK This script will create a new hotfix branch based off the 'master' branch for you to perform the fix.\n"

		Question "Do you wish to proceed?" "User declined to open a hotfix."
		printf "$USER Please enter a slug for this hotfix: "
		read HOTFIX_SLUG

		# Check the $HOTFIX_SLUG?

		git checkout -b "hotfix/"$(date +"%Y-%m-%d")"-$HOTFIX_SLUG" master
	fi
}

#
# Internal function for (re)building the database.
#
# @return void
#
function reloadDatabase()
{
#   Build the development database.
    mysql -u root -ppassword < /vagrant/vagrant/database.sql
#   And it's structure.
    php /vagrant/database/update.php
#   And then import some sample data.
    mysql -u root -ppassword kentprojects < /vagrant/tests/sample.sql
}

case "$1" in
    "circleci")
        case "$2" in
            "deployDevelop") circleCiDeployDevelop ;;
            "deployMaster") circleCiDeployMaster ;;
            *)
                printf "$FAIL Unknown CircleCi action"
                exit 6
                ;;
        esac
        ;;
	"deploy") deploy ;;
	"hotfix") hotfix ;;
	"reloadDatabase") reloadDatabase ;;
	"test")
	    cd tests/
	    ./run.sh
	    cd ..
	    ;;
	*)
		printf "A simple utility to help you work with the KentProjects codebase!\n\n"
		printf "Usage: ./kentprojects.sh ACTION\n"
		printf "deploy: Deploy this branch.\n"
		printf "hotfix: Working on a quick fix for the 'master' branch?.\n"
		printf "test: Run the KentProjects tests.\n"
		;;
esac
popd > /dev/null