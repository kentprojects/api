#!/bin/sh

BASE_PATH=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

case "$1" in
	"deploy") sh scripts/helper/deploy.sh ;;
	"hotfix") sh scripts/helper/hotfix.sh ;;
	"test") sh tests/run.sh ;;
	*)
		printf "A simple utility to help you work with the KentProjects codebase!\n\n"
		printf "Usage: ./kentprojects.sh ACTION\n"
		printf "test: Run the KentProjects tests.\n"
		;;
esac