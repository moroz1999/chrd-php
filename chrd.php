<?php
// All of the command line classes are in the Garden\Cli namespace.
use Garden\Cli\Cli;
use Chrd\Converter;
// Require composer's autoloader.
require_once 'vendor/autoload.php';

// Define the cli options.
$cli = new Cli();

$cli->description('CLI app for CHRD converter.')
    ->opt('source:s', 'Source PNG filename', true)
    ->opt('destination:d', 'Destination CHR$ filename', false);
// Parse and return cli args.
$args = $cli->parse($argv, true);
$source = $args->getOpt('source');
$destination = $args->getOpt('destination');

if (is_file($source)) {
    if (!$destination){
        $destination = pathinfo($source)['filename'].'.ch$';
    }
    try {
        $chrd = new Converter();
        $chrd->convert($source, $destination);
    } catch (\Exception $exception){
        echo $exception;
    }

}