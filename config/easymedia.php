<?php


return [

    /*
     * The filesystems on which to store added media. Choose one of the filesystems
     * you configured in app/config/filesystems.php
     */
    'storage_disk' => env('EASYMEDIA_STORAGE_DISK', 'easymedia'),

    /*
     * Default path generator class, used to generate the of files based on mapping
     */
    'default_path_generator' => \Webeleven\EasyMedia\Upload\DefaultPathGenerator::class

];
