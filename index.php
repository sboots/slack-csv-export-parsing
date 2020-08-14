<?php

# With composer we can autoload the Handlebars package
require_once ("./vendor/autoload.php");

$loader = new \Twig\Loader\FilesystemLoader(__DIR__."/templates");
$twig = new \Twig\Environment($loader, [
    'cache' => __DIR__."/template_cache",
    'debug' => true,
]);

$twig->addExtension(new \Twig\Extension\DebugExtension());


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

    // $htmlOutput = $handlebars->render("channel", ['conversation' => $conversation, 'data' => $data]);

    $htmlOutput = $twig->render('page.html', ['conversation' => $conversation, 'data' => $data]);




    file_put_contents("output/$conversation.html", $htmlOutput);
    exit();

    // foreach($data as $date => $items) {
    //     var_dump($items[0]);
    //     exit();
    // }
}