#!/bin/bash

PLEX_HOME=/media/Videos/Plex/XXX
PLAYLIST_DIR="${PLEX_HOME}/Playlists/"
DOWNLOAD_DIR="${PLEX_HOME}/Downloads/"
PORNHUB_DIR="${PLEX_HOME}/Pornhub/"
PREMIUM_DIR="${PORNHUB_DIR}/Premium/"
SORT_DIR="${PORNHUB_DIR}/Sort/"

function print_usage() {
   __genre_list_str=$(printf ",%s" "${__GENRE_LIST[@]}")
   __genre_list_str=${__genre_list_str:1}

   __usage="
Usage: $(basename "$0") [OPTIONS]

Options:
   -p,  --playlist   Run Playlist Command
   -s,  --sort       Run Mediaupdate in sort folder
   -m,  --move       Move downloaded files, update and sort
   -u,  --update     Run Media DB
"
   echo "$__usage"

}

for arg in "$@"; do
   shift
   case "$arg" in
   "--playlist") set -- "$@" "-p" ;;
   "--sort") set -- "$@" "-s" ;;
   "--move") set -- "$@" "-m" ;;
   "--update") set -- "$@" "-u" ;;
   *) set -- "$@" "$arg" ;;
   esac
done

# Parse short options
OPTIND=1

while getopts "psmu" opt; do

   case "$opt" in
   "p") __PLAYLIST=1 ;;
   "s") __SORT=1 ;;
   "m") __MOVE=1 ;;
   "u") __MEDIADB=1 ;;
   *)
      print_usage >&2
      exit 1
      ;;
   esac
done
shift $((OPTIND - 1))

shopt -s nocasematch

if [[ -n "${__PLAYLIST}" ]]; then
echo "Running Playlst CMD"
   cd "${PLAYLIST_DIR}"
   playlist pl -M5 list.txt
fi

if [[ -n "${__MOVE}" ]]; then
echo "Running Move CMD"
   cd "${DOWNLOAD_DIR}"
   mediadownload
   cd "${PREMIUM_DIR}"
   mediaupdate
   mediarename move -g
fi

if [[ -n "${__SORT}" ]]; then
echo "Running Sort CMD"
   cd "${SORT_DIR}"
   pwd
   mediaupdate
   mediarename move -g
fi

if [[ -n "${__MEDIADB}" ]]; then
echo "Running MedaDB CMD"
   cd "${PORNHUB_DIR}"
   mediadb
   mediadb all
fi
