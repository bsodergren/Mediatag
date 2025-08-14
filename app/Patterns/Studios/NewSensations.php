<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;

const NEWSENSATIONS_REGEX_COMMON = '//i';

class NewSensations extends Patterns
{
    public $regex = [
        'newsensations' => [
            'studio' => [
                'pattern' => '/^([a-zA-Z0-9-]+)_.*/i', ],
        ],
    ];

    public $network = 'New Sensations';

    public $replace_studios = [
        'ILoveMyMomsBigTits'         => 'I Love My Moms Big Tits',
        'momdoesitbest'              => 'Moms Does it Best',
        'pleasefmywife'              => 'Please f My Wife',
        'whenthehusbandlikestowatch' => 'When the Husband Likes to Watch',
        'whilehewatches'             => 'While he Watches',
        'adventuresofahotwife'       => 'Adventures of a Hotwife',
        'MILFStoriesStillSexy'       => 'MILF Stories Still Sexy',
        'MyFirstHotwifeExperience'   => 'My First Hotwife Experience',
        'ProudStagOfASexyVixen'      => 'Proud Stag Of A Sexy Vixen',
        'StagsVixens'                => 'Proud Stag Of A Sexy Vixen',
        'HotwifeTales'               => 'Hotwife Tales',
        'sexyhotwifestories'         => 'sexy hotwife stories',
        'WatchingMyHotwife'          => 'Watching My Hotwife',
        'SharingMyGirlfriend'        => 'Sharing My Girlfriend',
        'ILoveMyHotWife'             => 'I Love My HotWife',
        'mygirlwithotherguys'        => 'my girl with other guys',
        'NaughtyLittleSister'        => 'Naughty Little Sister',
        'marriedwithboyfriends'      => 'married with boyfriends',
        'mysexystepmom'              => 'my sexy stepmom',
        'myyounghotwife'             => 'my young hotwife',
        'MySexyHotwife'              => 'My Sexy Hotwife',
        'AHotwifeIsAHappyWife'       => 'A Hotwife Is A Happy Wife',
        'a-hotwife-blindfolded'      => 'a hotwife blindfolded',
        'frombothends'               => 'from both ends',
        '2headsrbetterthan1'         => '2 heads r better than 1',
        'MyWifesFirstBlowBang'       => 'My Wifes First BlowBang',
        'hotwiferubdown'             => 'hotwife rub down',
        'intheroomshesmyhotwife'     => 'in the room shes my hotwife',
        'my-hotwifes-gangbang'       => 'my hotwifes gangbang',
    ];

    public function metaStudio()
    {
        // utminfo(func_get_args());

        // UTMlog::Logger('Studio Key', $this->video_name);
        foreach ($this->replace_studios as $key => $value) {
            if (str_contains(strtolower($this->video_name), strtolower($key))) {
                return $value;
            }
        }

        return $this->network;
    }
}
