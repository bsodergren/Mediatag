#!/bin/bash

# Configuration
WATCH_DIR="/media/Videos/Plex/XXX/Studios" # Directory to monitor (relative or absolute path)
BIN_DIR="/home/bjorn/scripts/Mediatag/bin/"

MEDIADB_CMD="${BIN_DIR}mediadb" # Path to the media database command
MEDIAUPDATE_CMD="${BIN_DIR}mediaupdate" # Path to the media update command

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

OPTIONS=" "

function update_media_db {
    local file_path="$1"
    echo $(string.color "Updating media database for $file_path" "green" "black")
    # Placeholder for actual media database update command

    $MEDIADB_CMD $OPTIONS --path="$(dirname "$file_path")" -f="$file_path"
    $MEDIADB_CMD $OPTIONS --path="$(dirname "$file_path")" info
    $MEDIADB_CMD $OPTIONS --path="$(dirname "$file_path")" thumbnail
    # mediadb $OPTIONS --path="$(dirname "$file_path")" preview
}

function process_deleted_file {
    local dir_path="$1"
    echo $(string.color "Updating media database for $dir_path after deletion" "green" "black")
    $MEDIADB_CMD $OPTIONS --path="$dir_path"
        echo $(string.color "Updating media database for $dir_path after thumbnail deletion" "green" "black")

    $MEDIADB_CMD $OPTIONS --path="$dir_path" -c thumbnail
    # mediadb $OPTIONS --path="$dir_path" -c preview
}

function waitForUploadCompletion {
    local file_path="$1"
    local prev_size=-1
    while true; do
        current_size=$(stat -c%s "$file_path" 2>&1)
        exit_code=$?

        if [ ! $exit_code -eq 0 ]; then
            echo "failed"
            break
        fi
        if [[ "$current_size" -eq "$prev_size" ]]; then
            # echo "done"
            break
        fi
        prev_size=$current_size
        sleep 5
    done
    echo "done"
}

inotifywait -m -r --format '%:e,%w,%f' \
    --exclude '(-temp-|-data-)' \
    -e close_write,move,delete,create "$WATCH_DIR" |
    while IFS="," read -r events directory filename; do

        echo $(string.color "Change detected:$events " "red" "black")

        if [ "$events" = "DELETE" ]; then
            process_deleted_file "$directory"
            echo $(string.color "directory processed: $directory" "green" "black")
        fi

        if [ "$events" = "CREATE" ]; then
            FILE="${directory}$filename"

            res=$(waitForUploadCompletion "$FILE")
            # echo $(string.yellow "res res: $res")
            if [ "$res" = "done" ]; then

                echo $(string.color "Upload complete: $filename" "yellow" "black")
                $MEDIAUPDATE_CMD --path="$directory" -f="$FILE" $OPTIONS
                update_media_db "$FILE"
                echo $(string.color "Video processed: $filename" "green" "black") 

            fi
        fi
    done
