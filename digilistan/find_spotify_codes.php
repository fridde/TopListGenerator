<?php
mb_internal_encoding("UTF-8");
$ini_array = parse_ini_file("config.ini");
$artistsTitleArray = explode("\n", file_get_contents("total_list.txt"));
$currentFileName = "codes.txt";
$i = 0;
$saveArray = array();
$newStuffAdded = FALSE;
$startTime = microtime(TRUE);

foreach ($artistsTitleArray as $artistTitle) {
    $timeLeft = microtime(TRUE) - $startTime < $ini_array["maxTime"];
    if ($timeLeft) {
        if (strpos($artistTitle, "?!!?") === FALSE) {
            if (!(strpos($artistTitle, "###") === FALSE)) {
                $currentFileName = "completed_lists/" . preg_replace("%(#{3,})(.*)%", "\\2", $artistTitle) . ".txt";
                $saveArray[] = $artistTitle;

            }
            else {
                $artistTitle = preg_replace("%feat\. .* -%", "", $artistTitle);
                $artistTitle = preg_replace("%\(.*\)%", "", $artistTitle);
                // remove content in brackets
                $artistTitle = preg_replace("% - %", " ", $artistTitle);
                $artistTitle = str_replace("´", "'", $artistTitle);
                $artistTitle = str_replace("/", " ", $artistTitle);
                $artistTitle = str_replace(" ", "%20", $artistTitle);

                $url = "http://ws.spotify.com/search/1/track?q=" . $artistTitle;

                $htmlString = file_get_contents($url);
                preg_match_all("%(spotify:track:)(.*)(\">)%", $htmlString, $matches);
                // if spotify match found
                if (isset($matches[2][0])) {
                    file_put_contents($currentFileName, "spotify:track:" . $matches[2][0] . "\n", FILE_APPEND);
                    $newStuffAdded = TRUE;
                }
                // if no spotify match found
                else {
                    $saveArray[] = "?!!?" . $artistsTitleArray[$i];
                    $newStuffAdded = TRUE;
                }

            }

            sleep(0.1);
        }
        else {
            $saveArray[] = $artistsTitleArray[$i];
        }
    }
    // if no time left, just save the entry
    else {
        $saveArray[] = $artistsTitleArray[$i];
    }
    $i++;
}

file_put_contents("total_list.txt", implode("\n", $saveArray));

if (!$newStuffAdded) {
    echo "The whole list has been converted";
    $dir = opendir("completed_lists/");
    $fileArray = array();
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $fileArray[] = $file;
        }
    }
    closedir($dir);
    
    foreach ($fileArray as $fileName) {

        $artistsTitleArray = explode("\n", file_get_contents("completed_lists/" . $fileName));
        $cleanedArray = array();

        foreach ($artistsTitleArray as $artistsTitle) {
            if (!in_array($artistsTitle, $cleanedArray)) {
                $cleanedArray[] = $artistsTitle;
            }
        }
        file_put_contents("completed_lists/" . $fileName, implode("\n", $cleanedArray));
    }
}
?>