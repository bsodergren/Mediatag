#!/bin/bash

__PROJECT_HOME="/home/bjorn/scripts/Mediatag/var"
__SCRIPT__=$(basename $0)
__PROJECT__="Mediatag"
__INC_LIB_DIR="/home/bjorn/scripts/Mediatag/bin/inc"
source "${__INC_LIB_DIR}/header.sh"

BIN_DIR="/home/bjorn/scripts/Mediatag/bin/"

# Configuration
WATCH_DIR="/media/Videos/Plex/XXX/Studios" # Directory to monitor (relative or absolute path)
BIN_DIR="/home/bjorn/scripts/Mediatag/bin/"

MEDIADB_CMD="${BIN_DIR}mediadb"         # Path to the media database command
MEDIAUPDATE_CMD="${BIN_DIR}mediaupdate" # Path to the media update command

OPTIONS=""

function update_media_file {
    local filename="$1"
    local directory="$2"
    FILE="${directory}$filename"
    $MEDIAUPDATE_CMD --no-progress --path="$directory" -f="$FILE" $OPTIONS
    # echo "$MEDIAUPDATE_CMD --no-progress --path=$directory -f=$FILE $OPTIONS"
}

function update_media_db {
    local filename="$1"
    local directory="$2"
    FILE="${directory}$filename"

    filenameColored=$(string.yellow $filename)

    echo $(string.yellow "Updating media database for $filenameColored")
    # Placeholder for actual media database update command
    $MEDIADB_CMD --path="$directory" -f="$FILE"
    $MEDIADB_CMD --path="$directory" info
    $MEDIADB_CMD --path="$directory" thumbnail
    # mediadb $OPTIONS --path="$directory" preview
    echo $(string.green "Finished updating database for $filenameColored")
}

function process_deleted_file {
    local directory="$1"
    echo $(string.red "Updating media database for $directory after deletion")
    $MEDIADB_CMD  --path="$directory"
    echo $(string.red "Updating media database for $directory after thumbnail deletion")
    $MEDIADB_CMD --path="$directory" -c thumbnail
    # mediadb $OPTIONS --path="$directory" -c preview
}

function waitForUploadCompletion {
    local file_path="$1"
    local prev_size=-1
    while true; do
        current_size=$(stat -c%s "$file_path" 2>&1)
        exit_code=$?

        if [ ! $exit_code -eq 0 ]; then
            echo $(string.red "failed: $exit_code")
            break
        fi

        if [[ "$current_size" -eq "$prev_size" ]]; then
            echo $(string.green "finished: $current_size = $prev_size ")
            break
        else
            echo $(string.green "finished: $current_size,  $prev_size ")

            prev_size=$current_size
        fi
        sleep 10
    done
    echo "done"
}

PrevEvent=""
inotifywait -m -r --format '%:e,%w,%f' \
    --exclude '(-temp-|-data-|Premium|Sort)' \
    -e close_write,move,delete,create,moved_to "$WATCH_DIR" |
    while IFS="," read -r events directory filename; do

        # FILE="${directory}$filename"
        filenameColored=$(string.yellow $filename)
        echo $(string.red "Change detected: '$events' => $filenameColored")

        if [ "$events" = "DELETE" ]; then
            # process_deleted_file "$directory"
            echo $(string.green "directory processed: $directory")
        fi

        if [ "$events" = "CREATE" ]; then
            PrevEvent=$events
            # res=$(waitForUploadCompletion "$FILE")
            # echo $(string.yellow "res $res" "yellow" "black")
            # if [ "$res" = "done" ]; then
            #     echo $(string.green "directory CREATE: $directory" )
            # fi
        fi

        if [ "$PrevEvent" = "CREATE" ]; then
            if [ "$events" = "CLOSE_WRITE:CLOSE" ]; then
                echo $(string.blue "Upload complete: $filenameColored")
                update_media_file "$filename" "$directory"
                  sleep 10
                update_media_db "$filename" "$directory"
                echo $(string.green "Video processed: $filenameColored")
                PrevEvent=""
            fi
            # if [ "$events" = "MOVED_TO" ]; then
            #     update_media_db "$filename" "$directory"
            #     echo $(string.green "Video processed: $filenameColored")
            #     PrevEvent=""
            # fi
        fi
    done
