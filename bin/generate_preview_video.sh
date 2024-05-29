tmp_dir=$(mktemp -d -t {tmp_prefix}-XXXXXXXXXX)
echo $tmp_dir

width={width}
height={height}

frame_count=$(ffprobe -v error -show_entries format=duration "{input_filepath}" -of default=noprint_wrappers=1:nokey=1)
frame_target=$( expr ${{frame_count%.*}} / {output_frame_count})



ffmpeg -threads {threads} -i "{input_filepath}" -an -qscale:v 1 -vframes {output_frame_count} -f image2pipe -vcodec ppm \
    -vf "fps=1/$frame_target, scale=iw*min($width/iw\,$height/ih):ih*min($width/iw\,$height/ih):flags=lanczos, pad=$width:$height:($width-iw*min($width/iw\,$height/ih))/2:($height-ih*min($width/iw\,$height/ih))/2, unsharp=5:5:0.5:5:5:0.5" - \
| ffmpeg -y -threads {threads} -framerate {framerate} -i pipe:0 -c:v libx264 -profile:v baseline -level 3.0 -tune stillimage -r 30 -pix_fmt yuv420p "$tmp_dir/{output_filename}"

echo $tmp_dir/{output_filename}