<?php
/**
 * FileSystem service
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\FileSystem;

use Netric\Error\Error;
use Netric\Error\ErrorAwareInterface;
use Netric\Entity\ObjType\FileEntity;

/**
 * Create an image resizer service
 *
 * @package Netric\FileSystem
 */
class ImageResizer implements ErrorAwareInterface
{
    /**
     * File system service
     *
     * @var FileSystem
     */
    private $fileSystem = null;

    /**
     * Errors
     *
     * @var Error[]
     */
    private $errors = array();

    /**
     * Absolute path to a temp folder for working with files locally
     *
     * @var null|string
     */
    private $localTempPath = null;

    /**
     * List of temporary local files to cleanup
     *
     * @var string[]
     */
    private $tempFilesToClean = [];

    /**
     * Class constructor
     *
     * @param FileSystem $fileSystem File system service for manipulating netric files
     * @param string $localTempPath Local folder for storing temporary files
     */
    public function __construct(
        FileSystem $fileSystem,
        $localTempPath = '/tmp')
    {
        $this->fileSystem = $fileSystem;
        $this->localTempPath = $localTempPath;
    }

    /**
     * Make sure we did not leave any temp files laying around
     */
    public function __destruct()
    {
        foreach ($this->tempFilesToClean as $fileName) {
            @unlink($fileName);
        }
    }

    /**
     * Return the last logged error
     *
     * @return Error
     */
    public function getLastError()
    {
        return $this->errors[count($this->errors) - 1];
    }

    /**
     * Return all logged errors
     *
     * @return Error[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Copy an image resized to a specified path
     *
     * @param FileEntity $source The file to copy
     * @param int $maxWidth
     * @param int $maxHeight
     * @param string $toPath Where to put the image
     * @return FileEntity
     */
    public function resizeFile(FileEntity $source, $maxWidth=-1, $maxHeight=-1, $toPath=FileSystem::PATH_TEMP)
    {
        // First make sure it is an image
        $fileType = $source->getType();
        if (
            'jpg' !== $fileType &&
            'jpeg' !== $fileType &&
            'png' !== $fileType
        ) {
            throw new \RuntimeException("Only images can be resized. $fileType is not compatible.");
        }

        // Create a new name for the resized image
        $nameResized = $source->getName()
            . '-'
            . $source->getId()
            . '-'
            . $source->getValue('revision')
            . '-'
            . $maxWidth . 'x' . $maxHeight
            . '.'
            . $source->getType();

        // First check to see if the file exists and return it
        $exists = $this->fileSystem->openFile($toPath, $nameResized);
        if ($exists) {
            return $exists;
        }

        // Copy the file to a temp local file
        $localFilePath = $this->downloadToLocalTemp($source);

        // Get new height and width then resize it
        $imageSizes = $this->getOptimumDimensions($localFilePath, $maxWidth, $maxHeight);
        $resizedLocalFilePath = $this->resizeImage(
            $source,
            $localFilePath,
            $imageSizes['orig_width'],
            $imageSizes['orig_height'],
            $imageSizes['new_width'],
            $imageSizes['new_height']
        );

        // Upload to temp files for serving and future requests
        $resizedFileEntity = $this->fileSystem->importFile(
            $resizedLocalFilePath,
            FileSystem::PATH_TEMP,
            basename($resizedLocalFilePath)
        );

        // TODO: Copy permissions from source

        return $resizedFileEntity;
    }

    /**
     * Copy a file from the netric filesystem to a local temp file for processing
     *
     * @param FileEntity $source File to download
     * @return bool|string
     */
    private function downloadToLocalTemp(FileEntity $source)
    {
        $localTempName = tempnam($this->localTempPath, 'resizeimg');

        // Stream the file to a local copy
        $inputStream = FileStreamWrapper::open($this->fileSystem, $source);
        $outputStream = fopen($localTempName, 'w');
        fwrite($outputStream, stream_get_contents($inputStream));

        // Queue for cleanup
        $this->tempFilesToClean[] = $localTempName;

        return $localTempName;
    }

    /**
     * Create a local resized copy of an image
     *
     * @param FileEntity $originalFileEntity
     * @param string $localCopy Copy of the file on the local file system
     * @param int $origWidth The original width in px
     * @param int $origHeight The original height in px
     * @param int $newWidth The desired new width in px
     * @param int $newHeight The desired new height in px
     * @return string Path of resized image (it will be deleted on destruct)
     */
    private function resizeImage(
        FileEntity $originalFileEntity,
        $localCopy,
        $origWidth,
        $origHeight,
        $newWidth,
        $newHeight)
    {
        $processedImage = imagecreatetruecolor($newWidth, $newHeight);
        $imageResource = null;

        switch($originalFileEntity->getType())
        {
            case "jpg":
            case "jpeg":
            $imageResource = imagecreatefromjpeg($localCopy);
                break;
            case "gif":
                $imageResource = imagecreatefromgif($localCopy);
                break;
            case "png":
                imageAntiAlias($processedImage,true);
                imagealphablending($processedImage, false);
                imagesavealpha($processedImage,true);
                $transparent = imagecolorallocatealpha($processedImage, 255, 255, 255, 0);
                for($x=0;$x<$newWidth;$x++) {
                    for($y=0;$y<$newHeight;$y++) {
                        imageSetPixel($processedImage, $x, $y, $transparent);
                    }
                }
                $imageResource = imagecreatefrompng($localCopy);
                break;
            case "bmp":
                $imageResource = imagecreatefromwbmp($localCopy);
                break;
            default:
                break;
        }

        // Copy the image resized
        imagecopyresampled(
            $processedImage,
            $imageResource,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $origWidth,
            $origHeight
        );
        $resizedFilePath = $localCopy . "-res";

        // Create file from image resource
        switch ($originalFileEntity->getType())
        {
            case "jpg":
            case "jpeg":
                imagejpeg($processedImage, $resizedFilePath);
                break;
            case "gif":
                imagegif($processedImage, $resizedFilePath);
                break;
            case "png":
                imagepng($processedImage, $resizedFilePath);
                break;
            case "bmp":
                imagewbmp($processedImage, $resizedFilePath);
                break;
            default:
                break;
        }

        // Queue for cleanup
        $this->tempFilesToClean[] = $resizedFilePath;

        // Clean up image resource
        imagedestroy($processedImage);

        return $resizedFilePath;
    }

    /**
     * Calculate the ideal width and height based on limiting dimensions and current aspect ratio
     *
     * @param string $localFilePath Full path to a local image file
     * @param int $maxWidth
     * @param int $maxHeight
     * @param bool $stretch If set to true and the image is smaller than max*, then stretch it to max
     * @throws \RuntimeException if the file does not exist
     * @return array(int idealWidth, int idealHeight)
     */
    private function getOptimumDimensions($localFilePath, $maxWidth=-1, $maxHeight=-1, $stretch=false)
    {
        if (!file_exists($localFilePath)) {
            throw new \RuntimeException("$localFilePath does not exists or not accessible");
        }

        // Index 0 and 1 contains respectively the width and the height of the image
        list($currentWidth, $currentHeight) = getimagesize($localFilePath);

        $idealWidth = $currentWidth;
        $idealHeight = $currentHeight;

        // Constrain the height and width based on maxHeight
        if ($maxHeight > 0) {
            if (!$stretch && $currentHeight > $maxHeight) {
                $idealWidth = ($maxHeight / $currentHeight) * $currentWidth;
                $idealHeight = $maxHeight;
            } else if ($currentHeight) {
                $idealWidth = ($maxHeight / $currentHeight) * $currentWidth;
                $idealHeight = $maxHeight;
            }
        }

        // Constrain the height and width based on max width
        if ($maxWidth > 0) {
            if (!$stretch && $currentWidth) {
                if ($currentWidth > $maxWidth) {
                    $idealHeight = ($maxWidth / $currentWidth) * $currentHeight;
                    $idealWidth = $maxWidth;
                }
            }
            else if ($currentWidth) {
                $idealHeight = ($maxWidth / $currentWidth) * $currentHeight;
                $idealWidth = $maxWidth;
            }
        }

        return [
            'orig_height' => $currentHeight,
            'orig_width' => $currentWidth,
            'new_height' => $idealHeight,
            'new_width' => $idealWidth
        ];
    }
}