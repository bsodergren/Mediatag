import os
import shutil
import argparse


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("tmp_prefix", help="used to generate temporary dir to work in")
    parser.add_argument("input", help="path of input file")
    parser.add_argument("output_path", help="path to output result")
    parser.add_argument("output_filename", help="desired name of output file")
    parser.add_argument("-t", "--threads", type=int, default=2)
    parser.add_argument("--width", type=int, default=384, help="output width")
    parser.add_argument("--height", type=int, default=216, help="output height")
    parser.add_argument("--framerate", type=int, default=1, help="output video framerate")
    parser.add_argument("--seconds", type=int, default=10, help="length of output video")
    args = parser.parse_args()

    with open('generate_preview_video.sh', 'r') as file:
        generate_script = file.read()

    generate_previews_command = str(generate_script).format(
        threads=args.threads,
        tmp_prefix=args.tmp_prefix,
        width=args.width,
        height=args.height,
        framerate=args.framerate,
        output_frame_count=args.framerate*args.seconds,
        input_filepath=args.input,
        output_filename=args.output_filename
    )

    stream = os.popen(generate_previews_command)
    output = stream.readlines()

    tmp_dir = output[0].strip()
    file_path = output[2].strip()

    shutil.move(file_path, args.output_filename+ args.output_file)
    shutil.rmtree(tmp_dir)

if __name__ == "__main__":
    main()