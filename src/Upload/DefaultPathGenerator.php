<?php

namespace Webeleven\EasyMedia\Upload;

use Illuminate\Http\File;

class DefaultPathGenerator implements PathGenerator
{

    public function generate(File $file, $mapping, array $attributes = [])
    {
        if (! $mapping->isImageMapping()) {
            return sprintf('%s/%s.%s',
                $mapping->getMapper()->getBaseUploadDir(), $mapping->getFileName(), $file->extension());
        }

        $width = $attributes['width'];
        $height = $attributes['height'];
        $basePath = $mapping->getMapper()->getBaseUploadDir() . ($mapping->isConversion() ? '/conversions' : '');

        return sprintf('%s/%s_%sx%s.%s',
            $basePath,
            $mapping->getFileName(),
            $width,
            $height,
            $file->extension()
        );

    }
}