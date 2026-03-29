# systemctl stop mlsc.service

function function_name() {
	for i in "${FUNCNAME[@]:1}"; do
		rev=("$i" "${rev[@]}")
	done

	function_name=$(
		IFS="\\"
		echo "${rev[*]:1}"
	)
	#function_name=${function_name//source\\/}
	function_name=${function_name//\\main.log/}
	echo $function_name
}

function main.log() {
	local __txt=$1
	local __vars=$2
	local __func=$(string.green $(function_name) 0 1)
	local __line=$(string.purple ${BASH_LINENO} 0 1)
	local __txt=$(string.yellow "${__txt}" 0 1)

	if [ ! -z $__vars ]; then
		__vars=$(string.red ${__vars} 0 1)
		__vars="- $__vars"
	fi

	#echo  ${__txt}
	logr "info" "${__func}:${__line}::${__txt} ${__vars}" 

	#logr "info" "${BASH_LINENO}::${__txt}"

}

function run_cmd() {

	typeset cmd="$1"
	typeset ret_code

	#main.log "Running Command:"
	main.log "Running Command: " $cmd

	eval $cmd
	ret_code=$?

	if [ $ret_code == 0 ]; then
		echo "success" $ret_code
	else
		echo "failed " $ret_code

	fi

}

# string.trim
function string.trim() {
	local var=$1
	# remove leading whitespace characters
	var="${var#"${var%%[![:space:]]*}"}"
	# remove trailing whitespace characters
	var="${var%"${var##*[![:space:]]}"}"
	echo $var
}

function string.color() {

	local __string=$1
	local __background=$2
	local __RAINBOWPALETTE=$3

	__color=${FUNCNAME[1]##*.}

	case ${__background} in
	"black") __background_code=";40" ;;
	"red") __background_code=";41" ;;
	"green") __background_code=";42" ;;
	"yellow") __background_code=";43" ;;
	"blue") __background_code=";44" ;;
	"magenta") __background_code=";45" ;;
	"cyan") __background_code=";46" ;;
	"light gray") __background_code=";47" ;;
	"dark gray") __background_code=";100" ;;
	"light red") __background_code=";101" ;;
	"light green") __background_code=";102" ;;
	"light yellow") __background_code=";103" ;;
	"light blue") __background_code=";104" ;;
	"light magenta") __background_code=";105" ;;
	"light cyan") __background_code=";106" ;;
	"white") __background_code=";107" ;;
	*) __background_code=";49" ;;
	esac

	case ${__color} in
	"black") __color_code="$__RAINBOWPALETTE;30${__background_code}" ;;
	"red") __color_code="$__RAINBOWPALETTE;31${__background_code}" ;;
	"green") __color_code="$__RAINBOWPALETTE;32${__background_code}" ;;
	"yellow") __color_code="$__RAINBOWPALETTE;33${__background_code}" ;;
	"blue") __color_code="$__RAINBOWPALETTE;34${__background_code}" ;;
	"purple") __color_code="$__RAINBOWPALETTE;35${__background_code}" ;;
	"cyan") __color_code="$__RAINBOWPALETTE;36${__background_code}" ;;
	"light_gray") __color_code="$__RAINBOWPALETTE;37${__background_code}" ;;
	"dark_gray") __color_code="$__RAINBOWPALETTE;90${__background_code}" ;;
	"light_red") __color_code="$__RAINBOWPALETTE;91${__background_code}" ;;
	"light_green") __color_code="$__RAINBOWPALETTE;92${__background_code}" ;;
	"light_yellow") __color_code="$__RAINBOWPALETTE;93${__background_code}" ;;
	"light_blue") __color_code="$__RAINBOWPALETTE;94${__background_code}" ;;
	"light_magenta") __color_code="$__RAINBOWPALETTE;95${__background_code}" ;;
	"light_cyan") __color_code="$__RAINBOWPALETTE;96${__background_code}" ;;
	"white") __color_code="$__RAINBOWPALETTE;97${__background_code}" ;;
	*) __color_code="$__RAINBOWPALETTE;39${__background_code}" ;;
	esac

	echo -e "\e[${__color_code}m$1\e[0m"
}

## attr
## version: 1.0.1 - attribute name
##################################################
attr() {
	##################################################
	local attribute_name
	##################################################
	temp() {
		echo attr-${attribute_name}-$(date +%s)-${RANDOM}
	}
	#-------------------------------------------------
	main() {
		_() {
			[[ -d "/tmp/plexdata/" ]] || mkdir -p "/tmp/plexdata/"
			__tmp_colorfile="/tmp/plexdata/${2}"
			cat >${__tmp_colorfile} <<EOF
				string.${1}()
				{
					local __string="\$1"
					local __bg="\$2"
					local __rp="\$3"
					echo \$(string.color "\${__string}" "\${__bg}" "\${__rp}")
				}
EOF
			. ${__tmp_colorfile}

			#  rm ${__tmp_colorfile} --force #--verbose
		}
		_ "${attribute_name}" "$(temp)"
	}

	# 	main() {
	# 		_() {
	# 			cat > ${2} << EOF
	# 				string.${1}()
	# 				{
	# 					local __string="\$1"
	# 					local __bg="\$2"
	# 					local __rp="\$3"
	# 					echo \$(string.color "\${__string}" "\${__bg}" "\${__rp}")
	# 				}
	# EOF
	# 			. ${2}

	# 			rm ${2} --force #--verbose
	# 		} ; _ "${attribute_name}" "$( temp )"
	# 	}

	##################################################
	## $1 - attribute name
	##################################################
	if [ ${#} -eq 1 ]; then
		attribute_name=${1}
		main
	else
		exit 1 # wrong args
	fi
}
