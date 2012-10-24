<?php
if (defined('MODX_ASSETS_PATH')) {
    include MODX_ASSETS_PATH . 'mycomponents/mycomponent/core/components/mycomponent/model/mycomponent/mycomponentproject.class.php';
} else {
    include dirname(dirname(dirname(__FILE__))) . '/model/mycomponent/mycomponentproject.class.php';
}

/* set start time */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);
$mem_usage =  memory_get_usage();
$project = new MyComponentProject();


//$project->removeObjects();
$project->bootstrap();

// echo print_r(ObjectAdapter::$myObjects, true);

echo "\n\nInitial Memory Used: " . round($mem_usage / 1048576, 2) . " megabytes";
$mem_usage = memory_get_usage();
$peak_usage = memory_get_peak_usage(true);
echo "\nFinal Memory Used: " . round($mem_usage / 1048576, 2) . " megabytes";
echo "\nPeak Memory Used: " . round($peak_usage / 1048576, 2) . " megabytes";
/* report how long it took */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);
echo "\nTotal time: " . $totalTime;

