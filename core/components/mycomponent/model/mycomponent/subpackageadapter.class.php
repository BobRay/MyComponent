<?php


class SubpackageAdapter {
    public $packageDir = '';
    public $transportDir = '';
    public $transportFile = 'transport.subpackages.php';
    public $resolverDir = '';
    public $validatorDir = '';
    public $mode = MODE_BOOTSTRAP;
    /** @var $modx modX */
    public $modx;

    /** @var $helpers Helpers */
    public $helpers = null;



    final function __construct() {
    }
    public function createSubpackages(&$modx, &$helpers, $buildDir, $mode = MODE_BOOTSTRAP) {
        /** @var $modx modX */
        /** @var $helpers Helpers */
        /** @var $mode int */

        // $this->$modx =& $modx;
        $this->helpers =& $helpers;
        $this->mode = $mode;
        $this->packageDir = $buildDir . 'subpackages';
        $this->transportDir = $buildDir . 'data';
        $this->transportFile = 'transport.subpackages.php';
        $this->resolverDir = $buildDir . 'resolvers/packages';
        $this->validatorDir = $buildDir . 'validators';


        $helpers->resetFiles();

        $helpers->dirWalk($this->packageDir, '.zip');
        $files = $helpers->getFiles();
        if (empty($files)) {
            $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n    No Packages Found");
            return;
        }
        $subPackages = array();
        foreach ($files as $file => $path) {
            $signature = str_replace('.transport.zip', '', $file);
            $pieces = explode('-', $signature);
            $name = $pieces[0];
            $subPackages[$name] = $signature;
        }
        $this->createTransportFile($subPackages);
        $this->createResolvers($subPackages);
        $this->createValidators($subPackages);



    }
    public function createTransportFile($subPackages) {
        $tpl = $this->helpers->getTpl('transportfile.php');
        $tpl = str_replace('[[+elementType]]', 'Subpackage', $tpl);
        $tpl = $this->helpers->replaceTags($tpl);

        $tpl .=  <<<TEXT1
/** Package in subpackages
 *
 * @var modX \$modx
 * @var modPackageBuilder \$builder
 * @var array \$sources
 * @package articles
 */

TEXT1;

        $tpl .= '$subpackages = ';
        $tpl .= var_export($subPackages, true) .  ";\n";
$tpl .=  <<<TEXT2
\$spAttr = array('vehicle_class' => 'xPDOTransportVehicle');

foreach (\$subpackages as \$name => \$signature) {
    \$vehicle = \$builder->createVehicle(array(
        'source' => \$sources['subpackages'] . \$signature.'.transport.zip',
        'target' => "return MODX_CORE_PATH . 'packages/';",
    ), \$spAttr);
    \$vehicle->validate('php',array(
        'source' => \$sources['validators'].'validate.'.\$name.'.php'
    ));
    \$vehicle->resolve('php',array(
        'source' => \$sources['resolvers'].'packages/resolve.'.\$name.'.php'
    ));
    \$builder->putVehicle(\$vehicle);
}
return true;

TEXT2;


        if (!file_exists($this->transportDir . '/' . $this->transportFile) || $this->mode != MODE_BOOTSTRAP) {
            $this->helpers->writeFile($this->transportDir, $this->transportFile, $tpl);
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' .
                $this->helpers->modx->lexicon('mc_file_already_exists')
                . ': ' . $this->transportFile);
        }
    }

    public function createResolvers($subPackages) {
        $tpl = $this->helpers->getTpl('transportfile.php');
        $tpl .= <<<TEXT3

/**
 * Add [[+name]] package to packages grid
 *
 * @var modX \$modx
 * @var xPDOTransport \$transport
 * @var array \$options
 * @package [[+packageNameLower]]
 */
\$success= true;
if (\$transport && \$transport->xpdo) {
    \$signature = '[[+signature]]';
    \$modx =& \$transport->xpdo;
    \$modx->addPackage('modx.transport',\$modx->getOption('core_path').'model/');
    
    switch (\$options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            /* define version */
            \$sig = explode('-',\$signature);
            \$versionSignature = explode('.',\$sig[1]);

            /* add in the package as an object so it can be upgraded */
            /** @var modTransportPackage \$package */
            \$package = \$modx->newObject('transport.modTransportPackage');
            \$package->set('signature',\$signature);
            \$package->fromArray(array(
                'created' => date('Y-m-d h:i:s'),
                'updated' => date('Y-m-d h:i:s'),
                'installed' => strftime('%Y-%m-%d %H:%M:%S'),
                'state' => 1,
                'workspace' => 1,
                'provider' => 1,
                'disabled' => false,
                'source' => \$transport->signature . '/' . \$this->payload['class'] . '/' . \$this->payload['signature'] . '/' . \$signature.'.transport.zip',
                'manifest' => null,
                'package_name' => \$sig[0],
                'version_major' => \$versionSignature[0],
                'version_minor' => !empty(\$versionSignature[1]) ? \$versionSignature[1] : 0,
                'version_patch' => !empty(\$versionSignature[2]) ? \$versionSignature[2] : 0,
            ));
            if (!empty(\$sig[2])) {
                \$r = preg_split('/([0-9]+)/',\$sig[2],-1,PREG_SPLIT_DELIM_CAPTURE);
                if (is_array(\$r) && !empty(\$r)) {
                    \$package->set('release',\$r[0]);
                    \$package->set('release_index',(isset(\$r[1]) ? \$r[1] : '0'));
                } else {
                    \$package->set('release',\$sig[2]);
                }
            }
            \$success = \$package->save();
            \$modx->logManagerAction('package_install','transport.modTransportPackage',\$package->get('id'));
        break;
        
        case xPDOTransport::ACTION_UNINSTALL:
            /* remove the package on uninstall */
            \$package = \$modx->getObject('transport.modTransportPackage',array('signature' => \$signature));
            if (\$package) {
                if (\$package->uninstall()) {
                    /** @var modCacheManager \$cacheManager */
                    \$cacheManager= \$modx->getCacheManager();
                    \$cacheManager->refresh();
                    \$modx->logManagerAction('package_uninstall','transport.modTransportPackage',\$package->get('id'));
                }
            }
        break;
    }
}

return \$success;
TEXT3;
    foreach($subPackages as $name => $signature) {
        $content = $tpl;
        $content = str_replace('[[+elementType]] transport', 'Subpackage Resolver', $content);
        $content = str_replace('[[+name]]', $name, $content);
        $content = str_replace('[[+signature]]', $signature, $content);

        $content = $this->helpers->replaceTags($content);
        $dir = $this->resolverDir;
        $fileName = 'resolve.' . $name . '.php';
        if (!file_exists($dir . '/' . $fileName) || $this->mode != MODE_BOOTSTRAP) {
            $this->helpers->writeFile($dir, $fileName, $content);
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' .
                $this->helpers->modx->lexicon('mc_file_already_exists')
                . ': ' . $fileName);
        }
    }

    }

    public function createValidators($subPackages) {
        $tpl = $this->helpers->getTpl('transportfile.php');
        $tpl .= <<<TEXT4
        /**
 * Verify [[+name]] is latest or equal in version
 *
 * @var modX \$modx
 * @var xPDOTransport \$transport
 * @var array \$options
 * @package [[+packageNameLower]]
 */
\$newer= true;
if (\$transport && \$transport->xpdo) {
    switch (\$options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            \$modx =& \$transport->xpdo;

            /* define [[+name]] version */
            \$newVersion = '[[+version]]';
            \$newVersionMajor = '[[+versionMajor]]';
            \$name = '[[+name]]';

            /* now loop through packages and check for newer versions
             * Do not install if newer or equal versions are found */
            \$newer = true;
            \$modx->addPackage('modx.transport',\$modx->getOption('core_path').'model/');
            \$c = \$modx->newQuery('transport.modTransportPackage');
            \$c->where(array(
                'package_name' => \$name,
                'version_major:>=' => \$newVersionMajor,
            ));
            \$packages = \$modx->getCollection('transport.modTransportPackage',\$c);

            foreach (\$packages as \$package) {
                /** @var \$package modTransportPackage */
                if (\$package->compareVersion(\$newVersion)) {
                    \$newer = false;
                    break;
                }
            }
            break;
    }
}

return \$newer;

TEXT4;

        foreach ($subPackages as $name => $signature) {
            $content = $tpl;
            $content = str_replace('[[+elementType]] transport', 'Subpackage Validator', $content);
            $content = str_replace('[[+name]]', $name, $content);
            $version = str_replace($name . '-', '', $signature);
            $content = str_replace('[[+version]]', $version, $content);
            $versionMajor = substr($version, 0, 1);
            $content = str_replace('[[+versionMajor]]', $versionMajor, $content);

            $content = $this->helpers->replaceTags($content);
            $dir = $this->validatorDir;
            $fileName = 'validate.' . $name . '.php';
            if (!file_exists($dir . '/' . $fileName) || $this->mode != MODE_BOOTSTRAP) {
                $this->helpers->writeFile($dir, $fileName, $content);
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' .
                    $this->helpers->modx->lexicon('mc_file_already_exists')
                    . ': ' . $fileName);
            }
        }

    }
}