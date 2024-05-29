<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test;

use Mediatag\Core\Mediatag;
use Symfony\Component\Panther\Client;
use Mediatag\Modules\Executable\JsExec;
use Mediatag\Modules\Display\ShowDisplay;
use Nette\Utils\FileSystem as nFileSystem;
use Symfony\Component\HttpClient\HttpClient;
use SiteOrigin\StringSplitter\StringSplitter;
use Mediatag\Modules\VideoData\Data\VideoPreview;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem as SfSystem;

include_once __DATA_MAPS__.'/WordMap.php';

class Process extends Mediatag
{
    use Helper;
    
    public $VideoList = [];

    public $defaultCommands = [
        'exec' => null,
    ];

    public $commandList = [
    ];

    public $csvfilename = __DOWNLOAD_DIR__.'/pornhub.com-db.csv';

    public function __construct(InputInterface $input = null, OutputInterface $output = null, $args = null)
    {
        parent::boot($input, $output);

        // parent::$Display              = new ShowDisplay($output);
    }

    public function __call($m, $a)
    {
        return null;
    }

    public function exec($option = null)
    {
        $this->VideoList = parent::getVideoArray();
     //   
     foreach ($this->VideoList['file'] as $key => $videoInfo)
     {
        $preview = new VideoPreview;
       $previewLoc = $preview->BuildPreview($videoInfo);

       utmdd($previewLoc);

     }

        //echo $exec->stdout;
return 1;
        // $this->StorageConn = new Storage();

        //     // $client        = Client::createChromeClient();
        //     $client      = HttpClient::create();
        //     $webpage_url = 'https://www.pornhub.com/view_video.php?viewkey=ph61160ade8fd6b';
        //     $post_url    = 'https://www.pornhubpremium.com/front/authenticate';

        //     $login_url   = 'https://www.pornhubpremium.com/premium/login';
        //     $response    = $client->request('GET', $post_url, [
        //         'query'   => [
        //             'username' => 'Offended77',
        //             'password' => 'Sofie201$'],

        //         'headers' => [
        //             'Content-Type'     => 'application/x-www-form-urlencoded; charset=UTF-8',
        //             'Referer'          => $login_url,
        //             'X-Requested-With' => 'XMLHttpRequest',
        //         ],
        //     ]);
        //     utmdd([__METHOD__,$response]);

        //     $client->request('GET', $webpage_url);
        // $html = $client->getPageSource();

        // print_r(get_class_methods($scrape));

        // $i                   = 0;

        //        $string = implode("\n",$archiveArray);

        // $PlexdupePath        = __PLEX_HOME__.'/Dupes/'.__LIBRARY__.'/extra';
        // $PlexdupePath        =  nFileSystem::normalizePath($PlexdupePath);
        // nFileSystem::createDir($PlexdupePath, 0755);

        // foreach ($this->VideoList['dupe'] as $key => $videoInfo)
        // {
        //     $dupePath   = $videoInfo['video_path'];
        //     $filePath   = $this->VideoList['file'][$key]['video_path'];
        //     $video_file = null;
        //     if (str_ends_with($dupePath, 'New'))
        //     {
        //         $video_file = $videoInfo['video_file'];
        //         $video_path = $dupePath;
        //     }
        //     if (str_ends_with($filePath, 'New'))
        //     {
        //         $video_file = $this->VideoList['file'][$key]['video_file'];
        //         $video_path = $filePath;
        //     }

        //     if (null !== $video_file)
        //     {
        //         $video_name = basename($video_file);
        //         $dupeFile   = $PlexdupePath.'/'.$video_name;
        //         Mediatag::$Console->info(
        //             'Duplicate',
        //             ['Video' => $video_file],
        //             ['Path'  => $dupeFile]
        //         );
        //         (new SfSystem())->rename($video_file, $dupeFile, true);
        //     }
        // }

        // $this->getPhKeys();
    }

    public function print()
    {
    }
}
