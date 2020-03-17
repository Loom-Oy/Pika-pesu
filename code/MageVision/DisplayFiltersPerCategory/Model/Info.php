<?php
/**
 * MageVision Display Filters Per Category Extension
 *
 * @category     MageVision
 * @package      MageVision_DisplayFiltersPerCategory
 * @author       MageVision Team
 * @copyright    Copyright (c) 2019 MageVision (http://www.magevision.com)
 * @license      LICENSE_MV.txt or http://www.magevision.com/license-agreement/
 */
declare(strict_types=1);

namespace MageVision\DisplayFiltersPerCategory\Model;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Serialize\Serializer\Json;

class Info
{
    const MODULE_NAME = 'Display Filters Per Category';

    /**
     * @var Files
     */
    protected $files;

    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @param Files $files
     * @param DriverInterface $driver
     * @param Json $serializer
     */
    public function __construct(
        Files $files,
        DriverInterface $driver,
        Json $serializer
    ) {
        $this->files = $files;
        $this->driver = $driver;
        $this->serializer = $serializer;
    }

    /**
     * Returns extension version
     *
     * @return null|string
     *
     * @throws FileSystemException
     */
    public function getExtensionVersion(): ?string
    {
        $pathToNeededModule = '';

        $composerFilePaths = array_keys(
            $this->files->getComposerFiles(ComponentRegistrar::MODULE)
        );

        foreach ($composerFilePaths as $path) {
            if (strpos($path, 'MageVision/DisplayFiltersPerCategory/composer.json') !== false) {
                $pathToNeededModule = $path;
                break;
            }
        }

        if ($pathToNeededModule) {
            $content = $this->driver->fileGetContents($pathToNeededModule);
            if ($content) {
                $jsonContent = $this->serializer->unserialize($content);

                if (!empty($jsonContent['version'])) {
                    return $jsonContent['version'];
                }
            }
        }

        return null;
    }

    /**
     * Returns extension name
     *
     * @return string
     */
    public function getExtensionName(): string
    {
        return self::MODULE_NAME;
    }
}
