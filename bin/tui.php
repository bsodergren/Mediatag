#!/usr/bin/env php
<?php

/**
 * Command like Metatag writer for video files.
 */

define('__ROOT_DIRECTORY__', dirname(realpath($_SERVER['SCRIPT_FILENAME']), 2));
// define('__CONFIG_LIB__', __ROOT_DIRECTORY__ . '/config');

// xdebug_set_filter(
//     XDEBUG_FILTER_TRACING,
//     XDEBUG_PATH_EXCLUDE,
//     [__ROOT_DIRECTORY__ . '/vendor/'],
// );

// require __ROOT_DIRECTORY__ . '/bootstrap.php';
use Symfony\Component\Tui\Event\InputEvent;
use Symfony\Component\Tui\Input\Keybindings;
use Symfony\Component\Tui\Style\Style;
use Symfony\Component\Tui\Style\StyleSheet;
use Symfony\Component\Tui\Tui;
use Symfony\Component\Tui\Widget\InputWidget;
use Symfony\Component\Tui\Widget\TextWidget;

$tui = new Tui(new StyleSheet([
    ':root' => new Style(gap: 1),
]));

$keys = new Keybindings(['quit' => ['ctrl+c']]);
$tui->addListener(static function (InputEvent $event) use ($tui, $keys): void {
    if ($keys->matches($event->getData(), 'quit')) {
        $tui->stop();
    }
});

$input = new InputWidget();
$input->setPrompt('Name: ');
$input->onSubmit(fn () => $tui->stop());

$tui->add(new TextWidget('Enter your name and press Enter:'));
$tui->add($input);
$tui->setFocus($input);

$tui->run();

if ($input->wasSubmitted()) {
    echo 'Hello, '.$input->getValue()."!\n";
}
