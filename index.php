<?php

# With composer we can autoload the Handlebars package
require_once ("./vendor/autoload.php");

$loader = new \Twig\Loader\FilesystemLoader(__DIR__."/templates");
$twig = new \Twig\Environment($loader, [
    'cache' => __DIR__."/template_cache",
    'debug' => true,
]);

$twig->addExtension(new \Twig\Extension\DebugExtension());




// Thanks to
// https://stackoverflow.com/a/15488435

function dos2unix($s) {
    $s = str_replace("\r\n", "\n", $s);
    $s = str_replace("\r", "\n", $s);
    $s = preg_replace("/\n{2,}/", "\n\n", $s);
    return $s;
}


function csvstring_to_array($string, $separatorChar = ',', $enclosureChar = '"', $newlineChar = PHP_EOL) {
    // @author: Klemen Nagode
    $string = dos2unix($string);
    $array = array();
    $size = strlen($string);
    $columnIndex = 0;
    $rowIndex = 0;
    $fieldValue="";
    $isEnclosured = false;
    for($i=0; $i<$size;$i++) {

        $char = $string{$i};
        $addChar = "";

        if($isEnclosured) {
            if($char==$enclosureChar) {

                if($i+1<$size && $string{$i+1}==$enclosureChar){
                    // escaped char
                    $addChar=$char;
                    $i++; // dont check next char
                }else{
                    $isEnclosured = false;
                }
            }else {
                $addChar=$char;
            }
        }else {
            if($char==$enclosureChar) {
                $isEnclosured = true;
            }else {

                if($char==$separatorChar) {

                    $array[$rowIndex][$columnIndex] = $fieldValue;
                    $fieldValue="";

                    $columnIndex++;
                }elseif($char==$newlineChar) {
                    echo $char;
                    $array[$rowIndex][$columnIndex] = $fieldValue;
                    $fieldValue="";
                    $columnIndex=0;
                    $rowIndex++;
                }else {
                    $addChar=$char;
                }
            }
        }
        if($addChar!=""){
            $fieldValue.=$addChar;

        }
    }

    if($fieldValue) { // save last field
        $array[$rowIndex][$columnIndex] = $fieldValue;
    }
    return $array;
}




// Source for the CSV file
$filepath = "../slack-analysis/data/out/en-msgs.csv";


if (file_exists($filepath)) {


    // Thanks to
    // http://php.net/manual/en/function.str-getcsv.php#117692
    // $csv = array_map('str_getcsv', file($filepath));
    // array_walk($csv, function (&$a) use ($csv) {
    //     $a = array_combine($csv[0], $a);
    // });
    // array_shift($csv);

    $fileString = file_get_contents($filepath);
    $csv = csvstring_to_array($fileString);

    // var_dump($csv[0]);
    // exit();

    array_walk($csv, function (&$a) use ($csv) {
        $a = array_combine($csv[0], $a);
    });
    array_shift($csv);


// var_dump($csv);
// exit();

    if($csv) {
        foreach($csv as $item) {

            $item['date'] = substr($item['datetime'],0,10);

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

            $nestedArray[$item['conversation']][$item['root_msg_ts']][] = $item;


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