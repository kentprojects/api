#!/bin/sh

CURRENT_PATH=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
BASE_PATH=$(dirname $(dirname $CURRENT_PATH))

source "$CURRENT_PATH/functions.sh"
pushd "$BASE_PATH"

CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)

if [ "$CURRENT_BRANCH" != "master" ] || [[ $CURRENT_BRANCH != hotfix* ]]; then
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

popd