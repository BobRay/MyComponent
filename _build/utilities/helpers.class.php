<?php
/**
 * User: Bob Ray
 * Date: 8/2/12
 * Time: 10:17 PM
 * To change this template use File | Settings | File Templates.
 */

class Helpers

{
    var $replaceFields;
    var $tplPath;  /* path to MyComponent tpl directory */
    var $source;  /* path to root of MyComponent */
    var $dirPermission;

    function  __construct(&$modx, &$props = array())
    {
        $this->modx =& $modx;
        $this->props =& $props;
    }
    public function init() {
        $this->source = $this->props['source'];
        $this->tplPath = $this->source . '_build/utilities/' . $this->props['tplDir'] . '/';
        $this->dirPermission = $this->props['dirPermission'];
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
    public function replaceFields($text, $replaceFields = array()) {
        $replaceFields = empty ($replaceFields)? $this->replaceFields : $replaceFields;
        return $this->strReplaceAssoc($replaceFields, $text);
    }

    public function getTpl($name)
    {
        if (strstr($name, '.php')) { /* already has extension */
            $text = @file_get_contents($this->tplPath . $name);
        } else { /* use .tpl extension */
            $text = @file_get_contents($this->tplPath . $name . '.tpl');
        }
        return $text !== false ? $text : '';
    }

    public function makeFileName($elementObj, $elementType)
    {
        /* $elementType is in the form 'modSnippet', 'modChunk'm etc.
         * set default suffix to 'chunk', 'snippet', etc. */

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
            /* intentional fallthrough */
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

    public function stripPhpTags($filename)
    {
        $o = file_get_contents($filename);
        $o = str_replace('<?php', '', $o);
        $o = str_replace('?>', '', $o);
        $o = trim($o);
        return $o;
    }

    /*public function getReplaceFields() {
        return $this-replaceFields();

    }*/

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
