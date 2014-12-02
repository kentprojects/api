#!/bin/sh
FAIL=" \033[0;31;49m[==]\033[0m "
GOOD=" \033[0;32;49m[==]\033[0m "
WARN=" \033[0;33;49m[==]\033[0m "
TASK=" \033[0;34;49m[==]\033[0m "
USER=" \033[1;1;49m[==]\033[0m "

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