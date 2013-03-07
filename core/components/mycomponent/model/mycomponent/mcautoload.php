<?php

/**
 *  Load MC class files without an "include"
 *
 *

 *
 * @param string $class_name Class called by the user
 */
if (!function_exists('lower_basename')) {
    function lower_basename($string) {
        return strtolower(basename($string));
    }
}



if (! function_exists('mc_auto_load')) {
    function mc_auto_load($class_name) {
        static $files = null;

        $class_file = strtolower($class_name) . '.class.php';

        if (empty($files)) {
            $files = array();
            $dirs = array(
                dirname(__FILE__),
                /* may want to add controllers/processors dirs here */
            );

           /* $lower_basename = function ($string) {
                return strtolower(basename($string));
            };*/
            /*if (!function_exists('lower_basename')) {
                function lower_basename($string) {
                    return strtolower(basename($string));
                }
            }*/

            // For each directory, save the available files in the $files array.
            foreach ($dirs as $dir) {
                $glob = glob($dir . '/*.class.php');
                if ($glob === false || empty($glob)) continue;
                $fnames = array_map('lower_basename', $glob);
                $files = array_merge($files, array_combine($fnames, $glob));
            }
        }
        //$msg = print_r($files, true);
        //die($msg);
        // Search in the available files for the undefined class file.
        if (isset($files[$class_file])) {
            require($files[$class_file]);
            // If the class has a static method named __static(), execute it now, on initial load.
            if (class_exists($class_name, false) && method_exists($class_name, '__static')) {
                call_user_func(array(
                                    $class_name,
                                    '__static'
                               ));
            }
            $success = true;
        }
    }
}
