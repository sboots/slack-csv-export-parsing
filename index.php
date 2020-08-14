<?php

// Thanks to
// https://github.com/salesforce/handlebars-php#getting-started

# With composer we can autoload the Handlebars package
require_once ("./vendor/autoload.php");

# If not using composer, you can still load it manually.
# require 'src/Handlebars/Autoloader.php';
# Handlebars\Autoloader::register();

use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;

# Set the partials files
$partialsDir = __DIR__."/templates";
$partialsLoader = new FilesystemLoader($partialsDir,
    [
        "extension" => "html"
    ]
);

# We'll use $handlebars throughout this the examples, assuming the will be all set this way
$handlebars = new Handlebars([
    "loader" => $partialsLoader,
    "partials_loader" => $partialsLoader
]);


// Source for the CSV file
$filepath = "../slack-analysis/data/out/en-messages.csv";


if (file_exists($filepath)) {
    // Thanks to
    // http://php.net/manual/en/function.str-getcsv.php#117692
    $csv = array_map('str_getcsv', file($filepath));
    array_walk($csv, function (&$a) use ($csv) {
        $a = array_combine($csv[0], $a);
    });
    array_shift($csv);

    if($csv) {
        foreach($csv as $item) {

            $date = substr($item['datetime'],0,10);

            // Handlebars compatible booleans
            if($item['is_thread_start'] == "TRUE") {
                $item['is_thread_start'] = 1;
            }
            else {
                $item['is_thread_start'] = 0;
            }
            if($item['is_thread_reply'] == "TRUE") {
                $item['is_thread_reply'] = 1;
            }
            else {
                $item['is_thread_reply'] = 0;
            }

            $nestedArray[$item['conversation']][$date][$item['root_msg_ts']][] = $item;


            // var_dump($nestedArray);
            // exit();
        }
    }
}


foreach($nestedArray as $conversation => $data) {

    $htmlOutput = $handlebars->render("channel", ['conversation' => $conversation, 'data' => $data]);

    file_put_contents("output/$conversation.html", $htmlOutput);
    exit();

    // foreach($data as $date => $items) {
    //     var_dump($items[0]);
    //     exit();
    // }
}