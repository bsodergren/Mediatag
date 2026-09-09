<?php
require __DIR__.'/../vendor/autoload.php';

$common= new \Mediatag\Bundle\Dialog\Options\Common(
    ['backtitle', 'Testing Dialog...']
);

$n1 = new \Mediatag\Bundle\Dialog\Options\TreeNode('n1', 'Node 1');
$n11 = new \Mediatag\Bundle\Dialog\Options\TreeNode('n11', 'Node 1.1');
$n111 = new \Mediatag\Bundle\Dialog\Options\TreeNode('n111', 'Node 1.1.1');
$n12 = new \Mediatag\Bundle\Dialog\Options\TreeNode('n12', 'Node 1.2', true);
$n1->append($n11);
$n1->append($n12);
$n11->append($n111);
$n2 = new \Mediatag\Bundle\Dialog\Options\TreeNode('n2', 'Node 2');
$root = new \Mediatag\Bundle\Dialog\Options\TreeNode('root', 'Root', false, $n1, $n2);

$box = new \Mediatag\Bundle\Dialog\Widgets\Treeview('Selecte one node:', 0, $root);

$dialog = new \Mediatag\Bundle\Dialog\Dialog($common, $box);
$dialog->run();
echo PHP_EOL, 'Output is: ', $dialog->output(), PHP_EOL;
echo PHP_EOL, 'The node selected is: ', PHP_EOL;
print_r($box->node());

