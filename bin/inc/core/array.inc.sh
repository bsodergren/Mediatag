# shellcheck shell=bash disable=SC2034

shopt -s expand_aliases

alias array.getbyref='e="$( declare -p ${1} )"; eval "declare -A E=${e#*=}"'
alias array.foreach='array.keys ${1}; for key in "${KEYS[@]}"'

function array.print {
    array.getbyref
    array.foreach
    do
        echo "[$key]=(${E[$key]})"
    done | sort -rn -k3
}

function array.keys {
    array.getbyref
    KEYS=(${!E[@]})
}

function array.sort () {
	declare -n __unsorted_array="$1"
	declare -n __sorted_array="$2"

	oldIFS="$IFS"; IFS=$'\n'
	if [[ -o noglob ]]
	then
		setglob=1; set -o noglob
	else
		setglob=0
	fi

	__sorted_array=( $(printf '%s\n' "${__unsorted_array[@]}" |
            awk '{ print $NF, $0 }' FS='/' OFS='/' |
            sort | cut -d'/' -f2- ) )

	IFS="$oldIFS"; unset oldIFS
	(( setglob == 1 )) && set +o noglob
	unset setglob
}

function array.to.file()
{

	local __array_name=$1[@]
    local __array=("${!__array_name}")
	local __ARRAY_FILE="$2"
	local __CREATE_NEW="${3:-FALSE}"
	local __EXIT_MSG="${4:-}"


	if [[ $__CREATE_NEW == 1 ]]
	then
		if [[ ! -f $__ARRAY_FILE ]]
		then
			printf "%s\n" "${__array[@]}" > $__ARRAY_FILE

			if [[ $__EXIT_MSG != "" ]]
			then
				echo $__EXIT_MSG
				exit
			fi
		fi
	else
		main.log "$__ARRAY_FILE"
		printf "%s\n" "${__array[@]}" > $__ARRAY_FILE
	fi

	length=$(wc -c <$__ARRAY_FILE)
	if [ "$length" -ne 0 ] && [ -z "$(tail -c -1 <$__ARRAY_FILE)" ]
	then
		# The file ends with a newline or null
		dd if=/dev/null of=$__ARRAY_FILE obs="$((length-1))" seek=1 status=none
	fi
}

function array.search()
{
      local hay needle=$1
      shift

      for hay; do
          [[ $hay == $needle ]] && echo 0
      done
      echo 1

}
