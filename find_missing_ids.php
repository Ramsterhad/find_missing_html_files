<?php declare(strict_types=1);

// system requirements part 1/2.
if (php_sapi_name() !== 'cli') {
    echo 'Uhmm..excuse me?! I will not work with a stupid webserver. Call me on the CLI. See ya.';
    exit;
}

/**
 * @see https://www.php.net/manual/de/function.memory-get-usage.php#96280
 * @return string
 */
function getMemoryUsage()
{
    $unit=array('b','kb','mb','gb','tb','pb');
    $size = memory_get_usage();
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}


$memoryStart = getMemoryUsage();

$fileWithListOfAlreadyKnownIds = 'list_pages_already_downloaded.txt';
$basePathOfHtmlFiles = 'cosplay_tags/';
$resultFile = 'unknown_ids.txt';

// system requirements part 2/2.
if (!file_exists($fileWithListOfAlreadyKnownIds)) {
    echo PHP_EOL . 'Please put me right next to the file list_pages_already_downloaded.txt and call me again. Thank you! ฅ^•ﻌ•^ฅ' . PHP_EOL . PHP_EOL;
    exit;
}

if (!file_exists($basePathOfHtmlFiles)) {
    echo PHP_EOL . 'Please put me right next to the unzipped folder cosplay_tags and call me again. Thank you! ฅ^•ﻌ•^ฅ' . PHP_EOL . PHP_EOL;
    exit;
}

if (is_writable($resultFile)) {
    echo 'Could not create the output file "unknown_ids.txt"! Check the permissions.' . PHP_EOL . PHP_EOL;
}



/*
 * Remove the file extension ".html" from the known ids.
 * 665.html --> 665
 */
$knownIdsWithFileExtension = file($fileWithListOfAlreadyKnownIds);
$knownIds = array_map(function($id) {
    return (int) str_replace('.html', '', $id);
}, $knownIdsWithFileExtension);
unset($knownIdsWithFileExtension); // We do not need it anymore and saves us roughly 1,5mb.

/*
 * events / event / html files / link ids
 *
 * cosplay_tags/
 * |-Abunai 2013 Friday/
 * |---1.html
 * |---2.html
 * |-Abunai 2013 Saturday/
 * |---1.html
 * |---2.html
 */




$events = array_diff(scandir($basePathOfHtmlFiles), ['.', '..']);
$unknownIds = [];

foreach ($events as $event) {
    $htmlFiles = array_diff(scandir($basePathOfHtmlFiles . $event), ['.', '..']);

    foreach ($htmlFiles as $htmlFile) {
        $fileContent = file_get_contents($basePathOfHtmlFiles . $event . '/' . $htmlFile);
        preg_match_all('/view\/cosplay\/(\d+)/', $fileContent, $foundIds);

        /*
         * $foundIds = [
         *     0 => 'view/cosplay/665.html',
         *     1 => '665'
         * ]
         */
        if (isset($foundIds[1]) && !empty($foundIds[1])) {

            // We have found one or more ids. But do we know them already?
            foreach ($foundIds[1] as $foundling) {
                // First check if the foundling was already known. Then check if we already found him in the past.
                if (!in_array($foundling, $knownIds) && !in_array($foundling, $unknownIds)) {
                    $unknownIds[] = (int) $foundling;
                }
            }
        }
    }
}

file_put_contents($resultFile, implode(PHP_EOL, $unknownIds));

$memoryEnd = getMemoryUsage();

echo PHP_EOL;
echo 'MEMORY' . PHP_EOL;
echo '----------------' . PHP_EOL;
echo 'start: ' . $memoryStart . PHP_EOL;
echo 'end: ' . $memoryEnd . PHP_EOL;

echo PHP_EOL;
echo 'RESULT' . PHP_EOL;
echo '----------------' . PHP_EOL;
echo 'known ids  : ' . count($knownIds) . PHP_EOL;
echo 'unknown ids: ' . count($unknownIds) . PHP_EOL;
echo 'totaled ids: ' . (count($knownIds) + count($unknownIds)) . PHP_EOL;
echo PHP_EOL;
