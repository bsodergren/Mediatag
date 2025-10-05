<?php

namespace Mediatag\Bundle\Monitor;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;

/**
 * Inotify allows to listen to inotify events emit evenement events
 */
class Inotify extends EventEmitter
{
    /**
     * @var resource|false
     */
    protected $inotifyHandler = false;

    /**
     * @var array
     */
    protected $watchDescriptors = [];

    /**
     * @var LoopInterface
     */
    protected $loop;

    protected $wd_constants = [
        1          => ['IN_ACCESS', 'File was accessed (read)'],
        2          => ['IN_MODIFY', 'File was modified'],
        4          => ['IN_ATTRIB', 'Metadata changed (e.g. permissions, mtime, etc.)'],
        8          => ['IN_CLOSE_WRITE', 'File opened for writing was closed'],
        16         => ['IN_CLOSE_NOWRITE', 'File not opened for writing was closed'],
        32         => ['IN_OPEN', 'File was opened'],
        128        => ['IN_MOVED_TO', 'File moved into watched directory'],
        64         => ['IN_MOVED_FROM', 'File moved out of watched directory'],
        256        => ['IN_CREATE', 'File or directory created in watched directory'],
        512        => ['IN_DELETE', 'File or directory deleted in watched directory'],
        1024       => ['IN_DELETE_SELF', 'Watched file or directory was deleted'],
        2048       => ['IN_MOVE_SELF', 'Watch file or directory was moved'],
        24         => ['IN_CLOSE', 'Equals to IN_CLOSE_WRITE | IN_CLOSE_NOWRITE'],
        192        => ['IN_MOVE', 'Equals to IN_MOVED_FROM | IN_MOVED_TO'],
        4095       => ['IN_ALL_EVENTS', 'Bitmask of all the above constants'],
        8192       => ['IN_UNMOUNT', 'File system containing watched object was unmounted'],
        16384      => ['IN_Q_OVERFLOW', 'Event queue overflowed (wd is -1 for this event)'],
        32768      => ['IN_IGNORED', 'Watch was removed (explicitly by inotify_rm_watch() or because file was removed or filesystem unmounted'],
        1073741824 => ['IN_ISDIR', 'Subject of this event is a directory'],
        1073741840 => ['IN_CLOSE_NOWRITE', 'High-bit: File not opened for writing was closed'],
        1073741856 => ['IN_OPEN', 'High-bit: File was opened'],
        1073742080 => ['IN_CREATE', 'High-bit: File or directory created in watched directory'],
        1073742336 => ['IN_DELETE', 'High-bit: File or directory deleted in watched directory'],
        16777216   => ['IN_ONLYDIR', 'Only watch pathname if it is a directory (Since Linux 2.6.15)'],
        33554432   => ['IN_DONT_FOLLOW', 'Do not dereference pathname if it is a symlink (Since Linux 2.6.15)'],
        536870912  => ['IN_MASK_ADD', 'Add events to watch mask for this pathname if it already exists (instead of replacing mask).'],
        2147483648 => ['IN_ONESHOT', 'Monitor pathname for one event, then remove from watch list.'],
    ];

    /**
     * Constructor. Actual initialization takes place once first watched
     * paths is registered during add()
     *
     * @param  React\EventLoop\LoopInterface  $loop  Event Loop
     *
     * @see self::add()
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * Checks all new inotify events available
     * and emits them via evenement
     */
    public function __invoke()
    {
        if (false !== ($events = \inotify_read($this->inotifyHandler))) {
            $fcount = 0;

            foreach ($events as $event) {
                $fcount++;

                // make sure the watch descriptor assigned to this event is
                // still valid. removing watch descriptors via 'remove()'
                // implicitly sends a final event with mask IN_IGNORE set:
                // http://php.net/manual/en/inotify.constants.php#constant.in-ignored

                // echo "        inotify Event #".$fcount." - Object: ".$event['name'].": ".$this->wd_constants[$event['mask']][0]." (".$this->wd_constants[$event['mask']][1].")\n";

                if (isset($this->watchDescriptors[$event['wd']])) {
                    $path = $this->watchDescriptors[$event['wd']]['path'];
                    $this->emit($event['mask'], [$path . $event['name']]);
                }
            }
        }
    }

    /**
     * Adds a path to the list of watched paths
     *
     * @param  string  $path  Path to the watched file or directory
     * @param  int  $mask  Bitmask of inotify constants
     * @return int unique watch identifier, can be used to remove() watch later
     */
    public function add($path, $mask)
    {
        if ($this->inotifyHandler === false) {
            // inotifyHandler not started yet => start a new one
            $this->inotifyHandler = \inotify_init();
            stream_set_blocking($this->inotifyHandler, 0);

            // wait for any file events by reading from inotify handler asynchronously
            $this->loop->addReadStream($this->inotifyHandler, $this);
        }
        $descriptor                          = \inotify_add_watch($this->inotifyHandler, $path, $mask);
        $this->watchDescriptors[$descriptor] = ['path' => $path];

        return $descriptor;
    }

    /**
     * remove/cancel the given watch identifier previously aquired via add()
     * i.e. stop watching the associated path
     *
     * @param  int  $descriptor  watch identifier previously returned from add()
     */
    public function remove($descriptor)
    {
        if (isset($this->watchDescriptors[$descriptor])) {
            unset($this->watchDescriptors[$descriptor]);

            if ($this->watchDescriptors) {
                // there are still watch paths remaining => only remove this descriptor
                \inotify_rm_watch($this->inotifyHandler, $descriptor);
            } else {
                // no more paths watched => close whole handler
                $this->close();
            }
        }
    }

    /**
     * close the inotifyHandler and clear all pending events (if any)
     */
    public function close()
    {
        if ($this->inotifyHandler !== false) {
            $this->loop->removeReadStream($this->inotifyHandler);

            fclose($this->inotifyHandler);

            $this->inotifyHandler   = false;
            $this->watchDescriptors = [];
        }
    }
}
