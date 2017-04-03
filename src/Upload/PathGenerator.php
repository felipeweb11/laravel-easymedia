<?php

namespace Webeleven\EasyMedia\Upload;

use Illuminate\Http\File;

interface PathGenerator
{

    public function generate(File $file, $mapping, array $attributes = []);

}