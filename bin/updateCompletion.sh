#!/bin/bash


__media_cmds=("mediaupdate" "mediarename" "playlist" "mediadb" "mediaclip" "mediadownload" "mediashow")

for __media_cmd in ${__media_cmds[@]}
do
/home/bjorn/scripts/Mediatag/bin/${__media_cmd} completion bash | sudo tee /etc/bash_completion.d/${__media_cmd}

done
