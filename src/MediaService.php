<?php

namespace Webeleven\EasyMedia;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\File;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use Webeleven\EasyMedia\File as EasyFile;
use Webeleven\EasyMedia\Mapping\FileMapping;
use Webeleven\EasyMedia\Mapping\ImageMapping;
use Webeleven\EasyMedia\Upload\TempFileUploader;

class MediaService
{

    private $storage;

    private $tempFileUploader;

    private $intervention;

    private $transformer;

    public function __construct(
        Filesystem $storage,
        TempFileUploader $tempFileUploader,
        ImageManager $intervention,
        ImageTransformer $transformer
    ) {
        $this->storage = $storage;
        $this->tempFileUploader = $tempFileUploader;
        $this->intervention = $intervention;
        $this->transformer = $transformer;
    }

    public function makeMedia($file, $mapping)
    {
        $file = $this->tempFileUploader->getTempFile($file);

        if ($mapping instanceof ImageMapping) {
            return $this->makeImage($file, $mapping);
        }

        return$this->makeFile($file, $mapping);
    }

    protected function makeFile(File $file, FileMapping $mapping)
    {
        $mediaFile = new EasyFile();

        $filepath = $this->getFilepath($file, $mapping);

        $this->storage->put($filepath, file_get_contents($file));

        $mediaFile->name = $mapping->getFileName();
        $mediaFile->path = $filepath;
        $mediaFile->size = $file->getSize();
        $mediaFile->extension = $file->extension();
        $mediaFile->mimeType = $file->getMimeType();

        return $mediaFile;
    }

    protected function makeImage(File $file, ImageMapping $mapping)
    {
        $size = getimagesize($file->path());

        if (! $size) {
            throw new InvalidArgumentException('The given file does not is a image.');
        }

        $interventionImage = $this->getInterventionImage($file);

        $this->transformer->transformWith($interventionImage, $mapping);

        $width = $interventionImage->width();
        $height = $interventionImage->height();

        $filepath = $this->getFilepath($file, $mapping, compact('width', 'height'));

        $this->storage->put($filepath, $interventionImage->encode(null, $mapping->getQuality()));

        $image = new Image;
        $image->name = $mapping->getFileName();
        $image->size = $file->getSize();
        $image->width = $width;
        $image->height = $height;
        $image->path = $filepath;
        $image->extension = $file->extension();
        $image->mimeType = $file->getMimeType();

        if ($mapping->hasConversions()) {
            $this->makeConversions($image, $file, $mapping);
        }

        return $image;
    }

    protected function makeConversions($image, $file, ImageMapping $mapping)
    {
        $mapping->getConversions()->map(function($mapping, $name) use ($image, $file) {

            $interventionImage = $this->getInterventionImage($file);

            $this->transformer->transformWith($interventionImage, $mapping);

            $width = $interventionImage->width();
            $height = $interventionImage->height();

            $filepath = $this->getFilepath($file, $mapping, compact('width', 'height'));

            $this->storage->put($filepath, $interventionImage->encode(null, $mapping->getQuality()));

            $conversion = new Image;
            $conversion->name = $mapping->getFileName();
            $conversion->size = $interventionImage->filesize();
            $conversion->width = $width;
            $conversion->height = $height;
            $conversion->path = $filepath;
            $conversion->extension = $file->extension();
            $conversion->mimeType = $file->getMimeType();

            $image->addConversion($name, $conversion);

        });
    }

    protected function getFilepath($file, FileMapping $mapping, array $attributes = [])
    {
        $pathGenerator = $mapping->getPathGenerator();

        return $pathGenerator->generate($file, $mapping, $attributes);
    }

    protected function getInterventionImage($file)
    {
        return $this->intervention->make($file);
    }

}