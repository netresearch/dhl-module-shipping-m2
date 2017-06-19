<?php
/**
 * ExtensionProperties Phing Task
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */

class ExtensionProperties extends Task
{
    /**
     * Set extension name and version properties.
     */
    public function main()
    {
        $reader = new XMLReader();

        if (!$reader->open(__DIR__ . '/../../etc/module.xml')) {
            throw new BuildException("Failed to open 'module.xml'");
        }

        while ($reader->read()) {
            if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'module') {
                $this->project->setProperty('extension.name', $reader->getAttribute('name') . '_Module_M2');
                $this->project->setProperty('extension.version', $reader->getAttribute('setup_version'));
                break;
            }
        }
        $reader->close();

        $libDir = $this->project->getProperty('docker.LIB_HOST_PATH');
        $composerJson = @file_get_contents("$libDir/composer.json");
        if (!$libDir || !$composerJson) {
            throw new BuildException("Failed to read '$libDir/composer.json'");
        }

        $content = json_decode($composerJson, true);
        if ($content === null || !isset($content['version'])) {
            throw new BuildException("Could not read version from '$libDir/composer.json'");
        }

        $this->project->setProperty('library.name', 'Dhl_Shipping_Lib');
        $this->project->setProperty('library.version', $content['version']);
    }
}
