<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios\NewSensations;

use Mediatag\Modules\TagBuilder\Patterns;

const NEWSENSATIONS_REGEX_COMMON = '//i';

class NewSensations extends Patterns
{
    public $regex           = [
        'newsensations' => [
            'studio' => [
                'pattern' => '/^([a-zA-Z0-9-]+)_.*/i',
            ],
        ],
    ];

    public $network         = 'New Sensations';

    public $replace_studios = [
        '2headsrbetterthan1'         => '2 heads r better than 1',
        'AHotwifeIsAHappyWife'       => 'A Hotwife Is A Happy Wife',
        'HotwifeTales'               => 'Hotwife Tales',
        'HotWifeBlindFolded' => 'Hotwife BlindFolded',
        'HowToTrainaHotWife' => 'How To Train a HotWife',
        'ILoveMyHotWife'             => 'I Love My HotWife',
        'ILoveMyMomsBigTits'         => 'I Love My Moms Big Tits',
        'MILFStoriesStillSexy'       => 'MILF Stories Still Sexy',
        'MyBestFriendsWife' => 'My Best Friends Wife',
        'MyFirstHotwifeExperience'   => 'My First Hotwife Experience',
        'MyWifesFirstBlowBang'       => 'My Wifes First BlowBang',
        'NaughtyLittleSister'        => 'Naughty Little Sister',
        'SharingMyGirlfriend'        => 'Sharing My Girlfriend',
        'WatchingMyHotwife' => 'Watching My Hotwife',
        'WithMyHotwife' => 'With My Hotwife',
        'adventuresofahotwife'  => 'Adventures of a Hotwife',
        'frombothends'  => 'from both ends',
        'hotwiferubdown'    => 'hotwife rub down',
        'intheroomshesmyhotwife'    => 'in the room shes my hotwife',
        'marriedwithboyfriends' => 'married with boyfriends',
        'momdoesitbest' => 'Moms Does it Best',
        'mygirlwithotherguys'   => 'my girl with other guys',
        'mysexystepmom' => 'my sexy stepmom',
        'myyounghotwife'    => 'my young hotwife',
        'pleasefmywife' => 'Please f My Wife',
        'talesoftheyoungbusty' => 'Tales of the Young Busty',
        'watchingmygirlfriend' => 'Watching My Girlfriend',
        'watchingmystepsister' => 'Watching My Stepsister',
        'whenthehusbandlikestowatch' => 'When the Husband Likes to Watch',
    ];

    public function metaStudio()
    {
        // utminfo(func_get_args());
        // utmdd(\debug_backtrace());
        // UTMlog::Logger('Studio Key', $this->video_name);
        foreach ($this->replace_studios as $key => $value) {
            // utmdump([$this->video_name,$key]);
            if (str_contains(strtolower($this->video_name), strtolower($key))) {
                $this->studio = $value;

                return $value;
            }
        }

        return $this->network;
    }
}
