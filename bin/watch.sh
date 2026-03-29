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

PrevEvent=""
MovedFile="NO"
TEST="NO"
PreviousFilename=""
DEBUG="NO"
if [[ "$TEST" == "YES" ]]; then
    DEBUG="YES"
fi

function watch.debug {
    local text="$1"

    # echo $(string.cyan "$text")

}

function update_media_file {
    local filename="$1"
    local directory="$2"
    FILE="${directory}$filename"
    # echo $(string.blue "Video Metatags: $filenameColored")
    main.log "Running update_media_file: " $FILE

    if [[ "$TEST" == "NO" ]]; then
        $MEDIAUPDATE_CMD --silent --no-progress --path="$directory" -f="$FILE" $OPTIONS 
        # echo $(string.green "Video Metatags added: $filenameColored")
    fi
    # # echo "$MEDIAUPDATE_CMD --no-progress --path=$directory -f=$FILE $OPTIONS"
}

function update_media_db {
    local filename="$1"
    local directory="$2"
    FILE="${directory}$filename"
    # echo $(string.blue "Updating media database for $filenameColored")
    main.log "Running update_media_db: " $FILE
    # Placeholder for actual media database update command
    if [[ "$TEST" == "NO" ]]; then
        $MEDIADB_CMD --silent --path="$directory" -f="$FILE" 
        $MEDIADB_CMD --silent --path="$directory" info 
        $MEDIADB_CMD --silent --path="$directory" thumbnail 
        # mediadb $OPTIONS --path="$directory" preview
        # echo $(string.green "Finished updating database for $filenameColored")
    fi
}

function process_deleted_file {
    local directory="$1"
    # echo $(string.red "Updating media database for $directory after deletion")
    if [[ "$TEST" == "NO" ]]; then
        main.log "Running process_deleted_file: " $directory
        if [ -z "$(ls -A $directory)" ]; then
            directory="$(dirname "$directory")"
        fi

        $MEDIADB_CMD --silent --path="$directory" 
        $MEDIADB_CMD --silent --path="$directory" -c thumbnail 
        $MEDIADB_CMD --silent --path="$directory" -c preview 
    fi
}

# function runWatch {

    inotifywait -r -m --format '%:e,%w,%f,%T' \
        --exclude '(-temp-|-data-|Premium|Sort)' \
        --timefmt "%T"  \
        -e close_write,move,delete,create,moved_to "$WATCH_DIR" |
        while IFS="," read -r events directory filename time; do

            # -f "$filename" &&
            #

            FILE="${directory}$filename"
            main.log "Events on : " "$filename $events"
            filePrev="$(basename "$PreviousFilename")"
            fileNew="$(basename "$FILE")"

            if [[ "$filename" =~ \.part$ ]]; then
                filePart="$(basename "$FILE")"
            else
                filePart="$(basename "$FILE").part"
            fi

            if [[ "$PreviousFilename" != "$FILE" &&
                "$filePrev" != "$filePart" ]]; then

                if [[ "$DEBUG" == "YES" ]]; then

                    # echo $(watch.debug "$time: -----------------------")
                    # echo $(watch.debug "$time: $filePrev $fileNew $filePart")

                    filenameColoredRed=$(string.red $filename)
                    # echo $(watch.debug "$time: -----------------------")
                    # echo $(watch.debug "$time: NEW File Changes Detected $filenameColoredRed ")
                fi
                PrevEvent=""
                MovedFile="NO"
                PreviousFilename=$FILE

                SKIP_NEXT="NO"
                if [[ "$FILE" =~ \.part$ ]]; then
                    SKIP_NEXT="YES"
                fi
            fi
            # if [[ "$DEBUG" == "YES" ]]; then

            # echo $(string.blue "$time: '$events' MovedFile=>$MovedFile, SKIP_NEXT=?$SKIP_NEXT")
            # fi
            filenameColored=$(string.yellow $filename)

            if [[ "$SKIP_NEXT" == "NO" ]]; then

                if [[ "$DEBUG" == "YES" ]]; then

                    IFS=':' read -ra eventArray <<<"$events"

                    if [[ "${#eventArray[@]}" -gt 1 ]]; then
                        TAB=""
                        TEXT=""
                        for element in "${eventArray[@]}"; do
                            TEXT="$time: $TAB '$element' \n"
                            TAB="==>$TAB"
                            TEXT="$TEXT"
                            # # echo "$element"
                        done
                    else
                        TEXT="$time: '$events'"
                    fi

                    # echo $(watch.debug "${TEXT} => $filenameColored")
                fi

                if [[ "$events" == "CREATE" ]]; then
                
                    PrevEvent=$events
                fi
                if [[ "$PrevEvent" == "CREATE" ]]; then
                    if [[ "$events" == "CLOSE_WRITE:CLOSE" ]]; then
                        update_media_file "$filename" "$directory"
                        MovedFile=YES
                    fi
                fi

                if [[ "$events" == "MOVED_TO" ]]; then
                    if [[ "$MovedFile" == "NO" ]]; then
                        update_media_file "$filename" "$directory"
                        PrevEvent=""
                        MovedFile="YES"

                    fi
                fi

                if [[ "$events" == "MOVED_TO" || "$MovedFile" == "YES" ]]; then
                    if [[ "$MovedFile" == "YES" ]]; then
                        update_media_db "$filename" "$directory"
                        PrevEvent=""
                        MovedFile="NO"

                    fi
                fi

                if [[ "$events" == "DELETE" || "$events" == "MOVED_FROM" ]]; then
                    if [[ "$filename" =~ \.part$ && "$events" == "MOVED_FROM" ]]; then
                        SKIP_NEXT="YES"
                    else
                        process_deleted_file "$directory"
                    fi
                fi
            fi
            # else
            #     # echo "File '$filename' is NOT an MP4 file."
            # fi

        done

# }

# while true; do
# # 
#     $(runWatch)
#     exit_code=$?
#     main.log "exit_code: " $exit_code
#     # exiteak
#     # fi_code=$?
#     # if [ $exit_code -ne 0 ]; then
    
#     #     # echo "$exit_code exitted over "
#     #     br
# done

# exit_code=$?

# until $(runWatch); do
#     # echo "Still failing..."
#     sleep 1
# done

# if [[ $exit_code -eq 0 ]];then
# # echo "starting over "
# # echo
#     goto goto_label
# fi
