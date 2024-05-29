#!/bin/bash


if [ -z "$1" ]; then
    echo "usage: ./${0} VIDEO"
    exit
fi
PLEX_PATH="/home/bjorn/plex/XXX"
PREVIEW_PATH="/home/bjorn/www/plex_web/html/images/plex/vid_previews"
MOVIE=$1
# get video name without the path and extension
MOVIE_NAME=`basename $MOVIE`
OUT_DIR=`realpath ${MOVIE}`
OUT_DIR=${OUT_DIR/$MOVIE_NAME/}
OUT_DIR=${OUT_DIR/$PLEX_PATH/$PREVIEW_PATH}
OUT_FILENAME=`echo ${MOVIE_NAME%.*}_preview.mp4`
OUT_FILENAME=`echo "$OUT_DIR$OUT_FILENAME"`
mkdir -p "$OUT_DIR"
echo $OUT_FILENAME

width=384
height=216

frame_count=$(ffprobe -v error -show_entries format=duration "${MOVIE}" -of default=noprint_wrappers=1:nokey=1)
frame_target=$( expr ${frame_count%.*} / 10)
FFMPEG_CMD="ffmpeg -threads 2 -i \"${MOVIE}\" -an -qscale:v 1 -vframes 10 -f image2pipe -vcodec ppm -vf \"fps=1/$frame_target, scale=iw*min($width/iw\,$height/ih):ih*min($width/iw\,$height/ih):flags=lanczos, pad=$width:$height:($width-iw*min($width/iw\,$height/ih))/2:($height-ih*min($width/iw\,$height/ih))/2, unsharp=5:5:0.5:5:5:0.5\" -| ffmpeg -y -threads 2 -framerate 1 -i pipe:0 -c:v libx264 -profile:v baseline -level 3.0 -tune stillimage -r 30 -pix_fmt yuv420p \"${OUT_FILENAME}\""

eval $FFMPEG_CMD