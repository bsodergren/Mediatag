#!/bin/bash

PLEX_HOME=/media/Videos/Plex/XXX
PLAYLIST_DIR="${PLEX_HOME}/Playlists/"
DOWNLOAD_DIR="${PLEX_HOME}/Downloads/"
PORNHUB_DIR="${PLEX_HOME}/Pornhub/"
PREMIUM_DIR="${PORNHUB_DIR}/Premium/"
SORT_DIR="${PORNHUB_DIR}/Sort/"

if [[ -n "${1}" ]]; then
   cd "${SORT_DIR}"
   pwd

   exit
   mediaupdate
   mediarename move -g

   cd "${PORNHUB_DIR}"
   mediadb
   mediadb all

   exit
fi

cd "${PLAYLIST_DIR}"
playlist pl -M1 list.txt
cd "${DOWNLOAD_DIR}"
mediadownload
cd "${PREMIUM_DIR}"
mediaupdate
mediarename move -g
