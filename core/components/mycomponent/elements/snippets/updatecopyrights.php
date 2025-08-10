<?php

/* This will update the copyrights in all files for *ALL* your projects.
 *
 * It assumes that all copyrights are in one of these forms (with your name):
 *
 * Copyright 2014-2017 Bob Ray
 * Copyright 2014-2017 Bob Ray
 * Copyright 2014-2017 by Bob Ray
 *
*/

/** @var $modx modX */


/* Author name as is appears in existing copyright notices */
$author = 'Bob Ray';

$dryRun = false; // set to false to do the replacements
$debug = false; // set to true to see file array and $matches array for each file

/* Remove or add 'by' between last date and author
 * one of these should be true, the other should be false
 *
 * The settings below are recommended, since 'by' is not part of
 * a standard copyright declaration.
 *  */
$removeBy = true;
$addBy = false;
$upperCase = true; // Capitalize the C in Copyright

$currentYear = date("Y");

/* Optional -- sets copyright to current year + 1 */
// $currentYear = (int) strftime("%Y") + 1 ;
// $currentYear = (string) $currentYear;
$max = 1;  // Number of projects to operate on

/* Directory containing projects */
$projectDirectory = 'c:/xampp/htdocs/addons/assets/mycomponents/';

/* Directories to exclude (use full name of directory) */
$excludeDirs = '.git,.idea,.svn';

/* Exclude files with these patterns in their file names */
$excludeFiles = '.jpg,.jpeg,.gif,.png,.ttf,.svg,.less,.sass,.scss,.zip,.gitignore,.min,jsmin.,jsminplus,jquery,JQuery';

/* List of project directory names under assets/mycomponents directory */
$projects = array(
   // 'activationemail',
   // 'botblockx',
   //'cacheclear',
   // 'cachemaster',
  // 'canonical',
  // 'captcha',
  // 'caseinsensitiveurls',
   // 'classextender',
   // 'constantcontact',
   // convertdatabasecharset',
   //'defaultresourcegroup',
   //  'defaultusergroup',
   // 'dirwalker',
   // 'emailresource',
   // 'emptyalias',
   //'ezfaq',
   // 'fileupload',
   // 'fixedpre',
   // 'getDynaDescription',
   // 'GoRevo',
   // 'lexiconhelper',
   // 'loglogins',
   // 'logpagenotfound',
   // 'mandrillx',
   // 'messagemanager',
   // 'mycomponent',
   // 'newspublisher',
   // 'notify',
   'objectexplorer',
   // 'orphans',
   // 'personalize',
   // 'quickemail',
   // 'reflectblock',
   //  'refreshcache',
   // 'season',
   // 'siteatoz',
   // 'SiteCheck',
   // 'sizematters',
   // 'spform',
   // 'stagecoach',
   // 'subscribe',
   // 'syntaxhighlighter',
   // 'thermx',
   // 'upgrademodx',
);

/* Instantiate MODX if necessary */
if (!defined('MODX_CORE_PATH')) {
    $path1 = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/_build/build.config.php';
    if (file_exists($path1)) {
        include $path1;
    } else {
        $path2 = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config/config.inc.php';
        if (file_exists($path2)) {
            include($path2);
        }
    }
    if (!defined('MODX_CORE_PATH')) {
        session_write_close();
        die('[updatecopyrights.php] Could not find build.config.php');
    }
    require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
    $modx = new modX();
    /* Initialize and set up logging */
    $modx->initialize('mgr');
    $modx->getService('error', 'error.modError', '', '');
    $modx->setLogLevel(xPDO::LOG_LEVEL_INFO);
    $modx->setLogTarget(XPDO_CLI_MODE
        ? 'ECHO'
        : 'HTML');

    $prefix = $modx->getVersionData()['version'] >= 3
        ? 'MODX\Revolution\\'
        : '';

    /* This section will only run when operating outside of MODX */
    if (php_sapi_name() == 'cli') {
        /* Set $modx->user and $modx->resource to avoid
         * other people's plugins from crashing us */
        $modx->getRequest();
        $homeId = $modx->getOption('site_start');
        $homeResource = $modx->getObject($prefix . 'modResource', $homeId);

        if ($homeResource instanceof ($prefix . 'modResource')) {
            $modx->resource = $homeResource;
        } else {
            echo "\nNo Resource\n";
        }

    }

} else {
    if (!$modx->user->hasSessionContext('mgr')) {
        die ('Unauthorized Access');
    }
}

$modx->lexicon->load('mycomponent:default');

if (!class_exists('DirWalker')) {
    class DirWalker {
        /** @var $files array - array of files; created by dir_walk */
        protected $files = array();

        /** @var $props array - properties array */
        protected $props = array();

        /** @var array $includes - include files with these strings */
        protected $includes = array();

        /** @var bool $includesUseRegex - use a regex pattern for inclusion */
        protected $includesUseRegex = false;

        /** @var array $excludes - exclude files with these strings */
        protected $excludes = array();

        /** @var bool $excludesUseRegex - use a regex pattern for exclusion */
        protected $excludesUseRegex = false;

        /** @var array $excludeDirs - exclude directories with these strings */
        protected $excludeDirs = array();

        /**
         * If set, only filenames with these strings/patterns
         * will be included in the list.
         *
         * @param string $includes - comma-separated list of strings or patterns
         * @param bool $useRegex - use a regex pattern for the search
         */
        public function setIncludes($includes = '', $useRegex = false) {
            $this->includes = explode(',', $includes);
            $this->includesUseRegex = $useRegex;
        }

        /**
         * If set, filenames with these strings will be excluded
         * from the list.
         *
         * @param string $excludes - comma-separated list of strings or patterns
         * @param bool $useRegex - use a regex pattern for the search
         */
        public function setExcludes($excludes = '', $useRegex = false) {
            $this->excludes = explode(',', $excludes);
            $this->excludesUseRegex = $useRegex;
        }

        /**
         * Directories containing these strings (and their descendants)
         * will be excluded from the search.
         *
         * @param string $excludes - comma-separated list of strings
         */
        public function setExcludeDirs($excludes = '') {
            $this->excludeDirs = explode(',', $excludes);
        }

        /**
         * Test whether a filename matches an include pattern
         *
         * @param $file string - filename
         * @return bool
         */
        protected function hasIncludes($file) {
            $found = false;
            foreach ($this->includes as $string) {
                if ($this->includesUseRegex) {
                    if (preg_match($string, $file)) {
                        $found = true;
                    }
                } else {
                    if (strpos($file, $string) !== false) {
                        $found = true;
                    }
                }
            }
            return $found;
        }

        /**
         * Test whether a file matches an exclude pattern
         *
         * @param $file string - filename
         * @return bool
         */
        protected function hasExcludes($file) {
            $found = false;
            foreach ($this->excludes as $string) {
                if ($this->excludesUseRegex) {
                    if (preg_match($string, $file)) {
                        $found = true;
                    }
                } else {
                    if (strpos($file, $string) !== false) {
                        $found = true;
                    }
                }
            }
            return $found;
        }

        /**
         * Test whether a directory matches an exclude string
         *
         * @param $dir string - directory name
         * @return bool
         */
        protected function hasExcludeDirs($dir) {
            $found = false;
            foreach ($this->excludeDirs as $string) {
                if (strpos($dir, $string) !== false) {
                    $found = true;
                }
            }
            return $found;
        }

        /**
         * Recursively search directories for certain file types
         * Adapted from boen dot robot at gmail dot com's code on the scandir man page
         *
         * @param $dir - dir to search
         * @param bool $recursive - if false, only searches $dir, not it's descendants
         * @param string $baseDir - used internally -- do not send!
         */
        public function dirWalk($dir, $recursive = false, $baseDir = '') {
            /* remove trailing slash, if any, on the first pass */
            if (empty($baseDir)) {
                $dir = rtrim($dir, '/\\');
            }

            if ($dh = opendir($dir)) {
                /* Skip excluded directories, if any */
                if ((!empty($this->excludeDirs)) && ($this->hasExcludeDirs($dir))) {
                    closedir($dh);
                    return;
                }
                while (($file = readdir($dh)) !== false) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }
                    if (is_file($dir . '/' . $file)) {
                        /* Handle excludes, if any */
                        if ((!empty($this->excludes)) && $this->hasExcludes($file)) {
                            continue;
                        }
                        /* Handle includes, if any */
                        if ((!empty($this->includes)) && (!$this->hasIncludes($file))) {
                            continue;
                        }
                        /* Found one - add it to $this->files */
                        $this->processFile($dir, $file);

                    } elseif ($recursive && is_dir($dir . '/' . $file)) {
                        $this->dirWalk($dir . '/' . $file, $recursive, $baseDir . '/' . $file);
                    }
                }
                closedir($dh);
            }
        }

        /**
         * Default processor called by dirWalk() -- adds a found
         * file to $this->files in the form:.
         *
         * fullPath => fileName
         *
         * Override this method in a derived class to do
         * something else, or to process files as they are found.
         *
         * @param $dir string - directory of file (no trailing slash)
         * @param $file string - filename of file
         */
        protected function processFile($dir, $file) {
            $this->files[$dir . '/' . $file] = $file;
        }

        /**
         * Empties $this->files prior to dirWalk()
         */
        public function resetFiles() {
            $this->files = array();
        }

        /**
         * Get associative array of files found by dirWalk()
         *
         * @return array
         */
        public function getFiles() {
            return $this->files;
        }
    }
}

$dw = new DirWalker();
$dw->setExcludeDirs($excludeDirs);
$dw->setExcludes($excludeFiles);

// $max = 2;
// $pattern = '/(Copyright\s+)([0-9]{4})(\-)*\s*([0-9]*)\s*(by)*\s*Bob Ray/m';
$pattern = "/(Copyright)\s+([0-9]{4})(\-)*\s*([0-9]*)\s*([bB]y)*\s*{$author}/m";

/* Possible matches
    [0] => Copyright 2013-2017 by Bob Ray
    [1] => Copyright
    [2] => 2013
    [3] => -
    [4] => 2017
    [5] => by
*/


$i = 1;
foreach($projects as $project) {
    $dw->resetFiles();
    $projectPath = $projectDirectory . $project;
    $dw->dirWalk($projectPath, true);
    $files = $dw->getFiles();
    if ($debug) {
        echo "\n" . print_r($files, true);
    }
    foreach ($files as $filePath => $fileName) {
        $upToDate = false;

        $content = file_get_contents($filePath);
        if (strpos($content, 'Copyright') === false) {
            continue;
        }
        preg_match($pattern, $content, $matches);
        if (empty($matches)) {
            if ($dryRun) {
                echo "\n" . $filePath . ": Copyright found but no pattern match\n";
            }
            continue;
        }
        if ($debug) {
            echo print_r($matches, true);
        }
        if (isset($matches[2]) && $matches[2] == $currentYear) {
            $upToDate = true;
        }

        if (isset($matches[4]) && $matches[4] == $currentYear) {
            $upToDate = true;
        }
        $byFound = isset($matches[5]) && (!empty($matches[5]));

        if ($upToDate && $byFound && !$removeBy) {
            echo "\nAlready up to date " . $filePath . "\n";
            continue;
        }

        if ($upToDate && !$byFound && !$addBy) {
            if ($dryRun) {
                echo "\nAlready Up to Date: " . $filePath . "\n";
            }
            continue;
        }
        /* replacement required */

        $by = $addBy ? 'by ' : ($byFound && !$removeBy ? 'by ' : '');

        $matches[4] = $currentYear;
        $copyright = $upperCase? 'Copyright' : 'copyright';
        $final = "{$copyright} {$matches[2]}-{$currentYear} $by{$author}";

        if ($dryRun) {
            echo "\n" . $filePath;
            echo "\nORIGINAL: " . $matches[0];
            echo "\nFINAL: " . $final . "\n\n";
        } else {
            echo "\nUpdating: " . $filePath . "\n";
            $fp = fopen($filePath, 'w');
            if ($fp) {
                $content = str_replace($matches[0], $final, $content, $count);
                if ($debug) {
                    echo "\n" . $count . ' replacement';
                }
                if (fwrite($fp, $content)) {
                    echo "\nUpdated " . $filePath;
                } else {
                    echo "\nFailed to update " . $filePath;
                }
                fclose($fp);

            } else {
                echo "\nCould not open file for writing: " . $filePath . "\n";
            }
        }

    }
    $i++;
    if ($i >= $max) {
        break;
    }


}
