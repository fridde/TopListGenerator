<?php
mb_internal_encoding("UTF-8");
$configfilename = "config.ini";
$ini_array = parse_ini_file($configfilename);
$ini_array["ignoreDates"] = explode(",", $ini_array["ignoreDates"]);
$ini_array["years"] = explode(",", $ini_array["years"]);

$firstDateText = "1999-01-17";
$firstDate = strtotime($firstDateText);
$today = strtotime(date('Y-m-d') . "- 7 days");
$nextDate = $firstDate;
$nextDateText = date('Y-m-d', $nextDate);

$allDates = array($firstDateText);
while ($nextDate <= $today) {
    $nextDateText = date('Y-m-d', strtotime($nextDateText . " + 7 days"));
    $allDates[] = $nextDateText;
    $nextDate = strtotime($nextDateText);
}

$replacePatterns = explode("\r\n", file_get_contents("replace_patterns.txt"));

$pattern = '%(<span class=\"track-title\">)(.{1,})(</span>)%';

$startTime = microtime(TRUE);

$saveString = "";
$newStuffAdded = FALSE;

foreach ($allDates as $date) {
    $yearNow = reset(explode("-", $date));
    $timeLeft = microtime(TRUE) - $startTime < $ini_array["maxTime"];
    $newDate = !(in_array($date, $ini_array["ignoreDates"]));
    if ($newDate && $timeLeft) {
        if ($newDate) {
            $newStuffAdded = TRUE;
        }
        $url = "http://sverigesradio.se/sida/topplista.aspx?programid=2697&date=" . $date;
        $htmlString = file_get_contents($url);
        preg_match_all($pattern, $htmlString, $matches);
        $artistPlusTitleArray = array_unique($matches[2]);

        if (!in_array($yearNow, $ini_array["years"])) {
            $saveString .= "####" . $yearNow . "\n";
            $ini_array["years"][] = $yearNow;
        }

        foreach ($artistPlusTitleArray as $artistPlusTitle) {

            foreach ($replacePatterns as $thisPattern) {
                $thisPattern = explode(",", $thisPattern);
                $needle = $thisPattern[0];
                $replacement = $thisPattern[1];

                $artistPlusTitle = str_replace($needle, $replacement, $artistPlusTitle);
            }
            $artistPlusTitle = mb_strtolower($artistPlusTitle);
            $saveString .= $artistPlusTitle . "\n";
        }

        $ini_array["ignoreDates"][] = $date;

    }
}
file_put_contents("total_list.txt", $saveString, FILE_APPEND);

$text = "";
foreach ($ini_array as $key => $value) {
    if (gettype($value) == "array") {
        $value = implode(",", $value);
    }
    $text .= $key . " = " . $value . "\r\n";
}

file_put_contents($configfilename, $text);

if(!$newStuffAdded){
    $artistsTitleArray = explode("\n", file_get_contents("total_list.txt"));
    $cleanedArray = array();
    foreach($artistsTitleArray as $artistsTitle){
        if(!in_array($artistsTitle, $cleanedArray)){
            $cleanedArray[] = $artistsTitle;
        }
    }
    
    file_put_contents("total_list.txt", implode("\n", $cleanedArray));
    echo "No new tracks where found! <br> Performed cleanup of duplicate tracks.";
    
    
}

?>