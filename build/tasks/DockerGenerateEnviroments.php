<?php

/**
 * Netresearch_OPS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @copyright Copyright (c) 2017 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 *
 * DockerGenerateEnviroments.php
 *
 * @category  Task
 * @package   Netresearch_OPS
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 */
class DockerGenerateEnviroments extends Task
{
    const appVariable = '%app-version%';
    const phpVariable = '%php-version%';
    const nameVariable = '%project-name%';

    /**
     * @var string[] $files
     */
    private $files = ['.env', 'app/Dockerfile'];
    /**
     * @var string $appVersion
     */
    private $appVersion;

    /**
     * @var string $phpVersion
     */
    private $phpVersion = '7.0';

    /**
     * @var string $projectName
     */
    private $projectName;

    private $variables = [];

    /**
     * @param mixed $appVersion
     *
     * @returns $this
     */
    public function setAppVersion($appVersion)
    {
        $this->appVersion = $appVersion;
    }

    /**
     * @param mixed $phpVersion
     *
     * @returns $this
     */
    public function setPhpVersion($phpVersion)
    {
        $this->phpVersion = $phpVersion;
    }

    /**
     * @param mixed $projectName
     *
     * @returns $this
     */
    public function setProjectName($projectName)
    {
        $this->projectName = $projectName;
    }

    public function main()
    {
        $this->variables = [
            self::appVariable  => $this->appVersion,
            self::phpVariable  => $this->phpVersion,
            self::nameVariable => $this->projectName
        ];

        $targetPath = implode(
            DIRECTORY_SEPARATOR,
            [
                $this->project->getBasedir(),
                'docker',
                $this->appVersion,
                $this->phpVersion,
                ''
            ]
        );

        $message = sprintf(
            "Parsing docker enviroment files for %s, app version %s, PHP version %s",
            $this->projectName,
            $this->appVersion,
            $this->phpVersion
        );
        $this->log($message);
        foreach ($this->files as $file) {
            $path = $targetPath . $file;
            $content = file_get_contents($path);
            $content = str_replace(array_keys($this->variables), array_values($this->variables), $content);
            file_put_contents($path, $content);
        }
    }
}
