<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Filesystem Storage Disk
    |--------------------------------------------------------------------------
    |
    | The filesystems on which to store added media. Choose one of the
    | filesystems you configured in app/config/filesystems.php
    |
    */

    'storage_disk' => env('EASYMEDIA_STORAGE_DISK', 'easymedia'),

    /*
    |--------------------------------------------------------------------------
    | Default image path generator
    |--------------------------------------------------------------------------
    |
    | Default path generator class, used to generate the of files based on
    | mapping.
    |
    */

    'default_path_generator' => \Webeleven\EasyMedia\Upload\DefaultPathGenerator::class,

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | This package uses Intervention Image internally that supports "GD Library"
    | and "Imagick" to process images. You may choose one of them according to
    | your PHP configuration. By default PHP's "GD Library" implementation is
    | used.
    |
    | Supported: "gd", "imagick"
    |
    */

    'image_driver' => 'gd'

];
