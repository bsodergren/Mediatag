#!/bin/bash

DIR=$(pwd)

__media_cmds=("UpdateNewFiles.sh" "AddtoDb.sh" "ClearNewfiles.sh" "UpdateNewFiles.sh" "RemoveFromDb.sh" "AddtoDb.sh" "jsonDB.sh")

for __media_cmd in ${__media_cmds[@]}; do
    FILE=$DIR/${__media_cmd}
    if [ -e "$FILE" ]; then
    echo ${FILE}
        $FILE
    fi

done
