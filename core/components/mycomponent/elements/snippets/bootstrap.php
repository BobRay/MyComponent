<?php

include dirname(dirname(dirname(__FILE__))) . '/model/mycomponent/mycomponentproject.class.php';

$project = new MyComponentProject();
$project->bootstrap();
