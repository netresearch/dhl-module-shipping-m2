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
 * DockerProperties Phing Task
 *
 * @category  Task
 * @package   Netresearch_OPS
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 */
class DockerProperties extends Task
{
    const ENV_FILE = '.env';

    private $dir;

    /**
     * @param string $dir
     *
     * @returns $this
     */
    public function setDir($dir)
    {
        $this->dir = $dir;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function main()
    {
        $path = $this->project->getBasedir() . DIRECTORY_SEPARATOR . $this->dir . DIRECTORY_SEPARATOR . self::ENV_FILE;
        $content = file_get_contents($path);
        $variables = explode("\n", $content);
        array_walk(
            $variables,
            function ($value) {
                if (!empty($value)) {
                    list($k, $v) = explode('=', $value);
                    $this->project->setProperty('docker.' . $k, $v);
                }
            }
        );
    }
}
