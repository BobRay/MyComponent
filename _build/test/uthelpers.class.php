<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Bob Ray
 * Date: 8/17/12
 * Time: 4:17 AM
 * To change this template use File | Settings | File Templates.
 */
class UtHelpers
{

    function __construct()
    {

    }

    /** recursive remove dir function */
    public function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }

    }
}
