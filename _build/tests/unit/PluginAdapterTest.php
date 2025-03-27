<?php

namespace unit;

use CategoryAdapter;
use Codeception\Util\Fixtures;
use Helpers;
use modX;
use MyComponentProject;

class PluginAdapterTest extends \Codeception\Test\Unit {
    public string $objectName = 'plugins';
    public string $baseClass = 'modPlugin';
    public string $adapter = 'PluginAdapter';
    public string $codeFileSuffix = '.php';
    public modX $modx;
    public Helpers $helpers;
    public MyComponentProject $mc;
    public array $components;
    public array $objects;
    public string $class;
    public string $classPrefix;
    public array $categoryNames;

    public string $elementCodeBase = 'C:\xampp\htdocs\addons\assets\mycomponents\unittest\core\components\unittest\elements/';


    protected function _before(): void {
        $this->modx = Fixtures::get('modx');
        $modx = $this->modx;

        $this->classPrefix = $this->modx->getVersionData()['version'] >= 3
            ? '\MODX\Revolution\\'
            : '';

        $this->class = $this->classPrefix . $this->baseClass;

        $this->helpers = Fixtures::get('helpers');
        $this->components = require 'C:\xampp\htdocs\addons\assets\mycomponents\mycomponent\_build\config\unittest.config.php';
        $this->objects = $this->components['elements'][$this->objectName];
        $this->mc = Fixtures::get('myComponentProject');
        $this->mc->init(array(), 'unittest');
        $this->mc->createCategories();
        $this->categoryNames = array(
            'UnitTest',
            'utCategory2',
        );
        foreach ($this->categoryNames as $cat) {
            $c = $modx->getObject($this->classPrefix . 'modCategory', array('category' => $cat));
            assertNotEmpty($c);
        }
        /* Delete Objects from MODX */
        $objects = $this->components['elements'][$this->objectName];
        foreach ($objects as $key => $fields) {
            $name = $fields['name'] ?? $key;
            $object = $this->modx->getObject($this->class, array('name' => $name));
            if ($object) {
                assertNotFalse($object->remove(), 'Failed to remove ' . $this->baseClass . ': ' . $name);
            }
        }
    }

    protected function _after(): void {
        /* Delete Objects from MODX */

        $objects = $this->components['elements'][$this->objectName];
        foreach ($objects as $key => $fields) {
            $name = $fields['name'] ?? $key;
            $object = $this->modx->getObject($this->class, array('name' => $name));
            if ($object) {
                assertNotFalse($object->remove(), 'Failed to remove ' . $this->baseClass . ': ' . $name);
            }
        }

        if (!empty($codeFiles)) {
            foreach ($codeFiles as $codeFile) {
                unlink(strtolower($codeFile));
            }
        }

        if (!empty($transportFiles)) {
            foreach ($transportFiles as $transportFile) {
                unlink(strtolower($transportFile));
            }
        }
        $catClass = $this->classPrefix . 'modCategory';
        $query = $this->modx->newQuery($this->classPrefix . 'modCategory');
        $query->where(array('category:IN' => $this->categoryNames));
        $cats = $this->modx->getCollection($this->classPrefix . 'modCategory', $query);
        assertEquals(2, count($cats));
        foreach ($cats as $cat) {
            assertNotFalse($cat->remove());
        }
    }

    public function testFields() {
        assertTrue(true);
        assertTrue(is_array($this->components));

    }


    public function testBootstrap() {
        assertTrue(is_array($this->components));
        $objects = $this->objects;
        assertNotEmpty($objects);
        foreach ($objects as $object => $fields) {
            $fields['name'] = $fields['name'] ?? $object;
            $this->mc->addToModx($this->adapter, $fields);
            $object = $this->modx->getObject($this->class, array('name' => $fields['name']));
            assertNotEmpty($object);
            assertInstanceOf($this->class, $object);

            /* Make sure they have the correct category */
            $expectedCategory = $fields['category'];
            $categoryObj = $object->getOne('Category');
            assertEquals($expectedCategory, $categoryObj->get('category'),);

        }
    }


    public function testExport() {
        $this->helpers->init();
        $this->_createElements();
        $catNames = $this->categoryNames;

        foreach ($catNames as $catName) {
            $catObject = $this->modx->getObject($this->classPrefix . 'modCategory', array('category' => $catName));
            $fields = $catObject->toArray();
            $c = new CategoryAdapter($this->modx, $this->helpers, $fields, MODE_EXPORT);
            $c->exportElements(array($this->objectName));

            /* Store transport and code file names */
            $objs = $catObject->getMany(ucfirst($this->objectName));
            if (!empty($objs)) {
                $c::createTransportFile($this->helpers, $this->objects, $catName, $this->class, MODE_EXPORT);
                /* Check for transport file */
                $file = 'C:\xampp\htdocs\addons\assets\mycomponents\unittest\_build\data/' . $catName . '/transport.' . $this->objectName . '.php';
                assertFileExists($file, 'missing transport file: ' . $file);
                $transportFiles[] = $file;

                $contents = file_get_contents($file);
                assertNotEmpty($contents);

                /* Check for code file */
                $codeFiles = array();
                foreach ($objs as $obj) {
                    $alias = $this->helpers->getNameAlias($this->baseClass);
                    $type = rtrim($this->objectName, 's');
                    $codeFile = $this->elementCodeBase . $this->objectName . '/' . $obj->get($alias) . '.' . $type . $this->codeFileSuffix;
                    $codeFiles[] = $codeFile;
                    assertFileExists($codeFile, 'missing code file: ' . $codeFile);
                    $code = file_get_contents($codeFile);
                    assertNotEmpty($code, 'Code is empty');
                    $content = $obj->getContent();
                    assertStringNotContainsString('Updated', $content);
                    $content .= "\n<p>Updated</p>";
                    $obj->setContent($content);
                    assertNotFalse($obj->save());
                }
                /* Export again and check for updated content */
                $c->exportElements(array($this->objectName));

                foreach ($codeFiles as $codeFile) {
                    $newContent = file_get_contents($codeFile);
                    assertNotEmpty($newContent, 'New content is empty');
                    assertStringContainsString('Updated', $newContent);
                }
            }
        }
        foreach ($codeFiles as $codeFile) {
            assertTrue(unlink(strtolower($codeFile)));
        }

        foreach ($transportFiles as $transportFile) {
            assertTrue(unlink(strtolower($transportFile)));
        }
    }


    public function testImport() {
        $objectNames = [];
        $codeFiles = [];
        /* Prevent importing resources */
        $this->mc->props['exportResources'] = array();
        $this->_createElements(); /* Should create code files */
        $this->mc->importObjects($this->objectName, '', false);
        $cats = $this->categoryNames;

        /* Collect code file paths */
        foreach ($cats as $catName) {
            $catObject = $this->modx->getObject($this->classPrefix . 'modCategory', array('category' => $catName));
            assertNotEmpty($catObject);
            $fields = $catObject->toArray();
            $c = new CategoryAdapter($this->modx, $this->helpers, $fields, MODE_EXPORT);
            $c->exportElements(array($this->objectName));
            $objs = $catObject->getMany(ucfirst($this->objectName));
            if (!empty($objs)) {
                foreach ($objs as $obj) {
                    $objectNames[] = $obj->get('name');
                    $alias = $this->helpers->getNameAlias($this->baseClass);
                    $type = rtrim($this->objectName, 's');
                    $codeFile = $this->elementCodeBase . $this->objectName . '/' . $obj->get($alias) . '.' . $type . $this->codeFileSuffix;
                    $codeFiles[] = $codeFile;
                    assertFileExists($codeFile, 'missing code file: ' . $codeFile);
                    $code = file_get_contents($codeFile);
                    assertNotEmpty($code, 'Code is empty');
                    assertStringContainsString('goes here', $code);
                    assertStringNotContainsString('Updated', $code);
                    $code .= "\n<p>Updated</p>";
                    assertNotFalse(file_put_contents($codeFile, $code));
                }
            }

        }
        $this->mc->importObjects($this->objectName, '', false);

        foreach ($objectNames as $objectName) {
            $alias = $this->helpers->getNameAlias($this->baseClass);
            $actualObject = $this->modx->getObject($this->classPrefix . $this->class, array('name' => $objectName));
            assertNotEmpty($actualObject);
            $content = $actualObject->getContent();
            assertStringContainsString('Updated', $content);
        }

        foreach ($codeFiles as $codeFile) {
            assertTrue(unlink(strtolower($codeFile)));
        }

    }

    protected function _createElements($checkCategory = false) {
        $this->helpers->init();
        $catNames = $this->categoryNames;
        $objects = $this->helpers->props['elements'][$this->objectName];
        assertNotEmpty($objects);
        foreach ($objects as $object => $fields) {
            $fields['name'] = $fields['name'] ?? $object;
            $this->mc->addToModx($this->adapter, $fields);
            $object = $this->modx->getObject($this->class, array('name' => $fields['name']));
            assertNotEmpty($object);
            assertInstanceOf($this->class, $object);

            if ($checkCategory) {
                /* Make sure they have the correct category */
                $expectedCategory = $fields['category'];
                $categoryObj = $object->getOne('Category');
                assertEquals($expectedCategory, $categoryObj->get('category'),);
            }
        }

    }
}
