<?php

$knownIdsWithFileExtension = file('list_pages_already_downloaded.txt');
$knownIds = array_map(function($id) {
    return (int) str_replace('.html', '', $id);
}, $knownIdsWithFileExtension);
unset($knownIdsWithFileExtension);


$events = array_diff(scandir('cosplay_tags/'), ['.', '..']);
$unknownIds = [];

foreach ($events as $event) {
    $htmlFiles = array_diff(scandir('cosplay_tags/' . $event), ['.', '..']);

    foreach ($htmlFiles as $htmlFile) {
        $fileContent = file_get_contents('cosplay_tags/' . $event . '/' . $htmlFile);
        preg_match_all('/view\/cosplay\/(\d+)/', $fileContent, $foundIds);

        if (isset($foundIds[1]) && !empty($foundIds[1])) {

            foreach ($foundIds[1] as $foundling) {
                if (!in_array($foundling, $knownIds)) {
                    $knownIds[] = $foundling;
                    file_put_contents('unknown_ids_minified.txt', $foundling . PHP_EOL, FILE_APPEND);
                }
            }
        }
    }
}
