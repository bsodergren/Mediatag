#!/bin/bash

PLEX_HOME=/media/Videos/Plex/XXX
PLAYLIST_DIR="${PLEX_HOME}/Playlists/"
DOWNLOAD_DIR="${PLEX_HOME}/Downloaded/"
PORNHUB_DIR="${PLEX_HOME}/Pornhub/"
PREMIUM_DIR="${PORNHUB_DIR}/Premium/"
SORT_DIR="${PORNHUB_DIR}/Sort/"
NEW_DIR="${PORNHUB_DIR}/New/"

__MAX=5

function print_usage() {
   __genre_list_str=$(printf ",%s" "${__GENRE_LIST[@]}")
   __genre_list_str=${__genre_list_str:1}

   __usage="
Usage: $(basename "$0") [OPTIONS]

Options:
   -p,  --playlist   Run Playlist Command
   -m,  --move       Move downloaded files, update and sort
   -s,  --sort       Run Mediaupdate in sort folder
   -u,  --update     Run Media DB
      -f, --file

   "

   echo "$__usage"

}

for arg in "$@"; do
   shift
   case "$arg" in
   "--max") set -- "$@" "-M" ;;

   "--playlist") set -- "$@" "-p" ;;
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
   "M") __MAX=$OPTARG ;;

   "u") __MEDIADB=1 ;;
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
   cd "${PLAYLIST_DIR}"

   if [[ -z "${PLAYLIST_DIR}batch/${__FILE}" ]]; then
      for file in ${PLAYLIST_DIR}batch/mmf_384_videos_playlist/*; do
         echo "Loading Playlist ${file}"
         # playlist download -M2 -F ${file}
      done
   else
      echo "file exist"
      playlist download -M${__MAX} -F "${PLAYLIST_DIR}batch/${__FILE}"

   fi

}

function DoUpdate() {
   echo "The name of this function is: ${FUNCNAME[0]}"
   cd "${PREMIUM_DIR}"
   pwd
   mediadownload -o
   mediaupdate
   mediarename move -g
}

function runSortUpdate() {
   echo "Running Sort CMD"
   cd "${SORT_DIR}"
   mediaupdate
   mediarename move -g
   cd "${NEW_DIR}"
   mediaupdate
   mediarename move -g
}

if [[ -n "${__MOVE}" ]]; then
   echo "Running Move CMD"
   DoUpdate

fi
if [[ -n "${__SORT}" ]]; then
   runSortUpdate
fi

if [[ -n "${__MEDIADB}" ]]; then
   echo "Running MedaDB CMD"
   cd "${PORNHUB_DIR}"
   mediadb
   mediadb most
fi

if [[ -n "${__PLAYLIST}" ]]; then
   DoPlaylist
fi
