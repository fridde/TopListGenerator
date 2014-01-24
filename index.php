<?php

$ignoreArray = array();
if(isset($_REQUEST["ignore"])){
    $ignoreArray = explode(PHP_EOL, $_REQUEST["ignore"]);
}
array_walk($ignoreArray, "trim");

$firstDateText = "2013-04-28";
$firstDate = strtotime($firstDateText);
$today = strtotime(date('Y-m-d') . "- 7 days");
$nextDate = $firstDate;
$nextDateText = date('Y-m-d', $nextDate);


$allDates = array($firstDateText);
while($nextDate <= $today){
    $nextDateText =  date('Y-m-d', strtotime($nextDateText . " + 7 days"));   
    $allDates[] = $nextDateText;
    $nextDate = strtotime($nextDateText);
}
$allDates[] = "latest";

$bigHtmlString = "";

foreach($allDates as $date){
    $url = "http://charts.spotify.com/embed/charts/most_streamed/se/" . $date ;
    $bigHtmlString .= file_get_contents($url);
}

$pattern = "%(<a href=\"https://play\.spotify\.com/track/)(.{1,})(\" target=\"_blank\">)%";
preg_match_all($pattern, $bigHtmlString, $matches);

$codes = array_unique($matches[2]);

foreach($codes as $code){
    $thisString = "spotify:track:" . $code;    
    if(!in_array($thisString, $ignoreArray)){
        echo $thisString . "<br>";
    }
}


 ?>