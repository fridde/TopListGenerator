<?php
mb_internal_encoding("UTF-8");
$ini_array = parse_ini_file("config.ini");
/* create an array where each line becomes a member*/
$artistsTitleArray = explode("\n", file_get_contents("total_list.txt"));
/* better to have a default filename*/
$currentFileName = "codes.txt";
$i = 0;
/* since this script should not exceed server time limits, not all tracks can be converted each time.
 * Those lines of  "total_list.txt" that couldn't be converted to spotify trackCodes due to time-out
 * or no results are saved in $saveArray */
$saveArray = array();
$newStuffAdded = FALSE;
$startTime = microtime(TRUE);

foreach ($artistsTitleArray as $artistTitle) {
    $timeLeft = microtime(TRUE) - $startTime < $ini_array["maxTime"];
    if ($timeLeft) {
        /* all tracks that already have been shown to give no search results are prepended by "?!!?". We skip those*/
        if (strpos($artistTitle, "?!!?") === FALSE) {
            /* A new year is prepended by "###". We create a new file! */
            if (!(strpos($artistTitle, "###") === FALSE)) {
                $currentFileName = "completed_lists/" . preg_replace("%(#{3,})(.*)%", "\\2", $artistTitle) . ".txt";
                $saveArray[] = $artistTitle;
            }
            /* if we neither find a line that already has been searched for OR a line that contains a year,
             * we'll look for it on spotify
             * first the string is converted to a more "search friendly" version */
            else {
                // remove all "feat. Stupid artist"
                $artistTitle = preg_replace("%feat\. .* -%", "", $artistTitle);
                // stuff in brackets never helps
                $artistTitle = preg_replace("%\(.*\)%", "", $artistTitle);
                // the minus sign adds no information, better remove to be safe
                $artistTitle = preg_replace("% - %", " ", $artistTitle);
                // not sure whether ` or ' is the good way
                $artistTitle = str_replace("´", "'", $artistTitle);
                // is this necessary?
                $artistTitle = preg_replace("%[/&]%", " ", $artistTitle);
                // preparing for a good URL
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
            /* this is a time constraint spotify has given us. Max 10 searches per second per IP*/
            sleep(0.1);
        }
        /* as we already said, we don't want to search for strings we know to give no results, 
         * but they are worth saving anyway for future research  */
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

/* this rather uninteresting loop cleans all files of duplicates after everything is done*/
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