<?php
/**
 * User: Bob Ray
 * Date: 8/2/12
 * Time: 10:17 PM
 * To change this template use File | Settings | File Templates.
 */

class Helpers

{
    /* @var $replaceFields array */
    var $replaceFields;
    /* @var $tplPath string - path to MyComponent tpl directory */
    var $tplPath;
    /* @var $source string - path to root of MyComponent */
    var $source;
    /* @var $dirPermission - permission for new directories (from config file) */
    var $dirPermission;
    /* @var $filePermission - permission for new files (from config file) */
    var $filePermission;
    /* @var $modx modX - $modx object */
    var $modx;
    /* @var $props array - $scriptProperties array */
    var $props;

    function  __construct(&$modx, &$props = array()) {
        $this->modx =& $modx;
        $this->props =& $props;
    }
    public function init() {
        $this->source = $this->props['source'];
        $this->tplPath = $this->source . '_build/utilities/' . $this->props['tplDir'];
        if (substr($this->tplPath, -1) != "/") {
            $this->tplPath .= "/";
        }
        $this->dirPermission = $this->props['dirPermission'];
        $this->filePermission = $this->props['filePermission'];

        $this->replaceFields = array(
            '[[+packageName]]' => $this->props['packageName'],
            '[[+packageNameLower]]' => $this->props['packageNameLower'],
            '[[+author]]' => $this->props['author'],
            '[[+email]]' => $this->props['email'],
            '[[+copyright]]' => $this->props['copyright'],
            '[[+createdon]]' => $this->props['createdon'],
        );
        $license = $this->getTpl('license');
        if (!empty($license)) {
            $license = $this->strReplaceAssoc($this->replaceFields, $license);
            $this->replaceFields['[[+license]]'] = $license;
        }
        unset($license);
    }
    public function getReplaceFields() {
        return $this->replaceFields;
    }
    public function replaceTags($text, $replaceFields = array()) {
        $replaceFields = empty ($replaceFields)? $this->replaceFields : $replaceFields;
        return $this->strReplaceAssoc($replaceFields, $text);
    }

    /**
     * Get tpl file contents from MyComponent build tpl directory
     *
     * @param $name string  - Name of tpl file
     * @return bool|string
     */
    public function getTpl($name)
    {
        if (strstr($name, '.php')) { /* already has extension */
            $text = @file_get_contents($this->tplPath . $name);
        } else { /* use .tpl extension */
            $text = @file_get_contents($this->tplPath . $name . '.tpl');
        }
        return $text !== false ? $text : '';
    }

    public function makeFileName($elementObj, $elementType) {
        /* $elementType is in the form 'modSnippet', 'modChunk', etc.
         * set default suffix to 'chunk', 'snippet', etc. */
        /* ToDo: Get suffix from config file */
        /* @var $elementObj modElement */
        $suffix = substr(strtolower($elementType), 3);

        $extension = 'php';
        switch ($elementType) {
            case 'modTemplate':
                $name = $elementObj->get('templatename');
                $extension = 'html';
                break;
            case 'modChunk':
                $extension = 'html';
            /* intentional fall through */
            case 'modSnippet':
            case 'modPlugin':
                $name = $elementObj->get('name');
                break;
            case 'modResource':
                $name = $elementObj->get('pagetitle');
                $extension = 'html';
                $suffix = 'content';
                break;
            default:
                $name = '';
                break;

        }
        /* replace spaces with underscore */
        $name = str_replace(' ', '_', $name);
        return $name ? strtolower($name) . '.' . $suffix . '.' . $extension : '';

    }

    /**
     * Write a code file to disk - non-destructive -- will not overwrite existing files
     * Creates dir if necessary
     *
     * @param $dir string - directory for file (should not have trailing slash
     * @param $fileName string - file name
     * @param $content - file content
     * @param string $mode string - file mode; default 'w'
     */
    public function writeFile ($dir, $fileName, $content, $mode='w') {

        if (!is_dir($dir)) {
            mkdir($dir, $this->dirPermission, true);
        }
        /* add trailing slash if not there */
        if (substr($dir, -1) != "/") {
            $dir .= "/";
        }
        $file = $dir . $fileName;
        if (empty($content)) {
            $this->modx->log(MODX::LOG_LEVEL_ERROR, '    No content for file ' . $file);
        }

        $fp = fopen($file, 'w');
        if ($fp) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, '    Creating ' . $file);
            fwrite($fp, $content);
            fclose($fp);
            chmod($file, $this->filePermission);
        } else {
            $this->modx->log(MODX::LOG_LEVEL_INFO, '    Could not write file ' . $file);
        }


    }

    public function strReplaceAssoc(array $replace, $subject)
    {
        return str_replace(array_keys($replace), array_values($replace), $subject);
    }

    /**
     * Copies an entire directory and its descendants
     *
     * @param $source
     * @param $destination
     * @return bool
     */

    public function copyDir($source, $destination)
    {
        //echo "SOURCE: " . $source . "\nDESTINATION: " . $destination . "\n";
        if (is_dir($source)) {
            if (!is_dir($destination)) {
                mkdir($destination, $this->dirPermission, true);
            }
            $objects = scandir($source);
            if (sizeof($objects) > 0) {
                foreach ($objects as $file) {
                    if ($file == "." || $file == ".." || $file == '.git' || $file == '.svn') {
                        continue;
                    }

                    if (is_dir($source . '/' . $file)) {
                        $this->copyDir($source . '/' . $file, $destination . '/' . $file);
                    } else {
                        copy($source . '/' . $file, $destination . '/' . $file);
                    }
                }
            }
            return true;
        } elseif (is_file($source)) {
            return copy($source, $destination);
        } else {
            return false;
        }
    }
}
