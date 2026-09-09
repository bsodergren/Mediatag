<?php
require __DIR__.'/../vendor/autoload.php';

$common= new \Mediatag\Bundle\Dialog\Options\Common(
    ['backtitle', 'Testing Dialog...']
);

$box = new \Mediatag\Bundle\Dialog\Widgets\Mixedform('Form example:', 4,
    new \Mediatag\Bundle\Dialog\Options\MixedField('Normal', [1,0], '', [1,10], 30, 0, Mediatag\Bundle\Dialog\Options\MixedField::TYPE_NORMAL),
    new \Mediatag\Bundle\Dialog\Options\MixedField('Hidden', [2,0], '', [2,10], 10, 0, Mediatag\Bundle\Dialog\Options\MixedField::TYPE_HIDDEN),
    new \Mediatag\Bundle\Dialog\Options\MixedField('Read only', [3,0], 'hidden value', [3,10], 10, 0, Mediatag\Bundle\Dialog\Options\MixedField::TYPE_READONLY)
);

$dialog = new \Mediatag\Bundle\Dialog\Dialog($common, $box);
$dialog->run();
echo PHP_EOL, 'Output is: ', $dialog->output(), PHP_EOL;
echo PHP_EOL, 'Items selected are:', PHP_EOL;
print_r($box->items());
