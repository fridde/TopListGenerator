<form action="comparer.php" method="post">
	<input type="submit">
	<?php
    $requestArray = array(
        "recorded" => "Recorded Tracks",
        "toRecord" => "Tracks to record",
        "filtered" => "Filtered tracks"
    );
    
    foreach ($requestArray as $request => $title) {
        if (isset($_REQUEST[$request]) && $request != "filtered") {
            $$request = explode("\n", $_REQUEST[$request]);
            array_walk($$request, "trim");
        }
        else {
            $$request = array();
        }
        
    }
    foreach ($toRecord as $track) {
        if (!(in_array($track, array_values($recorded)))) {
            $filtered[] = $track;
        }
    }
    $filtered = array_unique($filtered);
    
    foreach ($requestArray as $request => $title) {
        echo "<h1>" . $title . "<h1>";
        echo '<textarea name="' . $request . '" rows="30" cols="50">';
        echo implode("\n", $$request);
        echo "\n";
        echo "</textarea>";
    }
?>
</form>