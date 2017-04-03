<?php

namespace Webeleven\EasyMedia;

use Intervention\Image\Image as InterventionImage;
use Webeleven\EasyMedia\Mapping\ImageMapping;

class ImageTransformer
{

    public function transformWith(InterventionImage $image, ImageMapping $mapping)
    {
        if ($mapping->shouldResize()) {
            $image->resize(
                $mapping->getWidth(),
                $mapping->getHeight(),
                function ($constraint) use ($mapping) {
                    if ($mapping->shouldKeepAspectRatio()) {
                        $constraint->aspectRatio();
                    }
                }
            );
        }

        if ($mapping->shouldResizeCanvas()) {
            $image->resizeCanvas(
                $mapping->getCanvasWidth(),
                $mapping->getCanvasHeight()
            );
        }

        return $image;
    }

}