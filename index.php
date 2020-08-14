<?php

# With composer we can autoload the Twig package
require_once ("./vendor/autoload.php");

require_once("helpers.php");

$loader = new \Twig\Loader\FilesystemLoader(__DIR__."/templates");
$twig = new \Twig\Environment($loader, [
    'cache' => __DIR__."/template_cache",
    'debug' => true,
]);

$twig->addExtension(new \Twig\Extension\DebugExtension());


// Source for the CSV file
$filepath = "../slack-analysis/data/out/en-msgs.csv";


if (file_exists($filepath)) {

    $fileString = file_get_contents($filepath);
    $csv = csvstring_to_array($fileString);

    // Thanks to
    // http://php.net/manual/en/function.str-getcsv.php#117692
    array_walk($csv, function (&$a) use ($csv) {
        $a = array_combine($csv[0], $a);
    });
    array_shift($csv);

    if($csv) {
        foreach($csv as $item) {

            $item['date'] = substr($item['datetime'],0,10);

            // Handlebars/Twig compatible booleans
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

            $nestedArray[$item['conversation']][$item['root_msg_ts']][] = $item;

        }
    }
}


foreach($nestedArray as $conversation => $data) {

    $htmlOutput = $twig->render('page.html', ['conversation' => $conversation, 'data' => $data]);

    file_put_contents("output/$conversation.html", $htmlOutput);
    
    echo "Exported output/$conversation.html\n";
}

echo "Finished exporting all channels.\n\n";
