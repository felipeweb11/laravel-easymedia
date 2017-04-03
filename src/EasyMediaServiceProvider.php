<?php

namespace Webeleven\EasyMedia;

use Illuminate\Support\ServiceProvider;
use Webeleven\EasyMedia\Upload\TempFileUploader;

class EasyMediaServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/easymedia.php' => config_path('easymedia.php'),
        ], 'config');
    }

    public function register()
    {
        $this->app->singleton(MediaService::class, function() {

            $storage = $this->getStorageDriver();

            return new MediaService(
                $storage,
                new TempFileUploader(),
                $this->app['image'],
                new ImageTransformer
            );
        });

        $this->mergeConfigFrom(__DIR__.'/../config/easymedia.php', 'easymedia');
    }

    protected function getStorageDriver()
    {
        $disk = $this->app['config']->get('easymedia.storage_disk');

        return $this->app['filesystem']->disk($disk);
    }

}