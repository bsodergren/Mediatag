import { blendVideos } from "../node_modules/ffmpeg-transitions/dist/index"





const videoPaths = [
    "/media/Videos/Plex/Clips/Studios/Thagson/MMF/SheLikesThreesomes3_s03_DahliaSky_MarkZane_720p_h264_Blowjob_0.mp4",
    "/media/Videos/Plex/Clips/Studios/Thagson/MMF/SheLikesThreesomes3_s03_DahliaSky_MarkZane_720p_h264_cumshot_1.mp4",
    "/media/Videos/Plex/Clips/Studios/Thagson/MMF/FamilyTabooIndecentYoungsters2_s02_AnnadeVille_NikkiNuttz_720p_Blowjob_0.mp4"
    // Add more video paths as needed
];

const output = "/media/Videos/Plex/XXX/Studios/Home Videos/Compilation/test.mp4";

// For a single transition type for all videos
const transition = 'slideleft'; // Transition type

// For different transitions between each video
// const transition = [
//     { transition: 'fade', duration: 0.5 },
//     { transition: 'slideleft', duration: 0.5 }
//     // Add more transitions as needed
// ];

const transitionDuration = 2; // Transition duration in seconds, used if a single transition type is provided

blendVideos(videoPaths, output, transition, transitionDuration, (err, result) => {
    if (err) {
        console.error('Error concatenating videos:', err);
        return;
    }
    console.log('Videos concatenated successfully:', result);
});