<?php
//exit('Not work!');
require __DIR__ . '/../vendor/autoload.php';

$common = new \Mediatag\Bundle\Dialog\Options\Common(
    ['backtitle', 'Testing Dialog...']
);

$step1 = new \Mediatag\Bundle\Dialog\Options\StepItem('Proccess 1');
$step2 = new \Mediatag\Bundle\Dialog\Options\StepItem('Proccess 2');
$step3 = new \Mediatag\Bundle\Dialog\Options\StepItem('Proccess 3');
$box = new \Mediatag\Bundle\Dialog\Widgets\Mixedgauge('Total progress...', 0, $step1, $step2, $step3);

$dialog = new \Mediatag\Bundle\Dialog\Dialog($common, $box);


//run dialog
$total_progress = 0;
foreach ($box->items() as $i => $step){
    $box->percent($total_progress);
    $step->running(true);
    $partial_percent = 0;
    //simulate step proccess
    while($partial_percent < 100){
        $step->percent($partial_percent);
        $dialog->run();
        $partial_percent += 10;
        sleep(1);
    }
    $partial_percent = ($partial_percent > 100)? 100 : $partial_percent;
    if($partial_percent == 100){
        $partial_percent = rand(0, 6);//simulate final status
        $step->running(false);
    }
    $step->percent($partial_percent);
    $total_progress += 33;
    $box->percent($total_progress);
    $dialog->run();
    //end simulate process
}
//finalize
$box->percent(100);
$dialog->run();