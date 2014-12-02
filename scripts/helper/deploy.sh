#!/bin/sh

CURRENT_PATH=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

if [ -z "$BASE_PATH" ]; then
	BASE_PATH=$(dirname $(dirname $CURRENT_PATH))
fi

source "$CURRENT_PATH/functions.sh"
pushd "$BASE_PATH"

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
git merge --no-ff $SOURCE_BRANCH -m "Merging develop branch with master branch." &&
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

popd