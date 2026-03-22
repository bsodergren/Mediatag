# shellcheck shell=bash disable=SC1090

# failsafe - fall back to current directory
[ "$DIR" == "" ] && DIR="."

pushd () {
    command pushd "$@" > /dev/null
}

popd () {
    command popd "$@" > /dev/null
}

export pushd popd
export OLDIFS

declare -A OPTION_ARRAY
export OPTION_ARRAY

function options.get()
{
	local __varname=$1

	if test "${OPTION_ARRAY[$__varname]+isset}"
	then
		[[ "${OPTION_ARRAY[$__varname]}" != "" ]] && echo ${OPTION_ARRAY[$__varname]}; return
	fi
}

function options.set()
{
	local __varname=$1
	local __value=$2
	OPTION_ARRAY[$__varname]=$__value
}

__INC_CORE_DIR="${__INC_LIB_DIR}/core"

OLDIFS=$IFS
IFS=$'\n'
mapfile -t __inc_core_dir_array < <(find "${__INC_CORE_DIR}" -type f -name "*.inc.sh")


sLen=${#__inc_core_dir_array[@]}
for ((i = 0; i < sLen; i++))
do
	source "${__inc_core_dir_array[$i]}"
done


__PROJECT_INC_DIR="${__PROJECT_HOME}/inc"
__PROJECT_HEADER="${__PROJECT_INC_DIR}/header.inc.sh"
[[ -f $__PROJECT_HEADER ]] && source "$__PROJECT_HEADER"


__logr_LOG_DIR="${__PROJECT_HOME}/log"

[[ -z "${__logr_LOG_NAME}" ]] && __logr_LOG_NAME=$(basename "$0")
logr start  "${__logr_LOG_NAME}"
logr clear
logr info "starting loggin"


## Setup color functions, string.color


__bash_colors=("red" "green" "blue" "yellow" "purple")

for __bash_color in ${__bash_colors[@]}
do
	attr $__bash_color
done
