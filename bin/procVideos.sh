#!/bin/bash

PLEX_HOME=/media/Videos/Plex/XXX
PLAYLIST_DIR="${PLEX_HOME}/Playlists/"
DOWNLOAD_DIR="${PLEX_HOME}/Downloads/"
PORNHUB_DIR="${PLEX_HOME}/Pornhub/"
PREMIUM_DIR="${PORNHUB_DIR}/Premium/"
SORT_DIR="${PORNHUB_DIR}/Sort/"

__FILE="list.txt"
__MAX=5

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
   -f, --file
   "

   echo "$__usage"

}

for arg in "$@"; do
   shift
   case "$arg" in
   "--playlist") set -- "$@" "-p" ;;
   "--max") set -- "$@" "-M" ;;
   "--sort") set -- "$@" "-s" ;;
   "--move") set -- "$@" "-m" ;;
   "--update") set -- "$@" "-u" ;;
   "--file") set -- "$@" "-f" ;;
   *) set -- "$@" "$arg" ;;
   esac
done

# Parse short options
OPTIND=1

while getopts "psmuM:f:" opt; do

   case "$opt" in
   "p") __PLAYLIST=1 ;;
   "s") __SORT=1 ;;
   "m") __MOVE=1 ;;
   "u") __MEDIADB=1 ;;
   "M") __MAX=$OPTARG ;;
   "f") __FILE=$OPTARG ;;
   *)
      print_usage >&2
      exit 1
      ;;
   esac
done
shift $((OPTIND - 1))

shopt -s nocasematch

function DoPlaylist() {
    echo "The name of this function is: ${FUNCNAME[0]}"
   cd "${PLAYLIST_DIR}"
   playlist pl -M${__MAX} ${__FILE}

}

if [[ -n "${__PLAYLIST}" ]]; then
   DoPlaylist
fi

if [[ -n "${__MOVE}" ]]; then
   echo "Running Move CMD"
   cd "${DOWNLOAD_DIR}"
   mediadownload
   cd "${PREMIUM_DIR}"
   mediaupdate
   mediarename move -g
fi

if [[ -n "${__PLAYLIST}" ]] || [[ -n "${__MOVE}" ]]; then
   exit
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
