<?php

namespace Webeleven\EasyMedia;

use Webeleven\EasyMedia\Common\CastsValueObjects;
use Webeleven\EasyMedia\Mapping\MediaMapper;

trait EasyMediaTrait
{
    use CastsValueObjects;

    protected $mapper;

    protected function getValueObjects()
    {
        return $this->mapMediaFields();
    }

    public function getMediaMapper()
    {
        if ($this->mapper !== null) {
            return $this->mapper;
        }

        return $this->mapper = new MediaMapper($this);
    }

    protected function createValueObject($key, $value)
    {
        if ($value instanceof File) {
            return $value;
        }

        if (is_string($value) && ($data = json_decode($value, true)) !== null) {
            $class = $this->getValueObjects()[$key];
            return new $class($data);
        }

        $service = app(MediaService::class);

        $this->mapMedia($this->getMediaMapper());

        $mapping = $this->getMediaMapper()->findMapping($key);

        return $service->makeMedia($value, $mapping);
    }

    protected function mapMediaFields()
    {
        if (! property_exists($this, 'media') || ! is_array($this->media)) {
            return [];
        }

        return collect($this->media)->mapWithKeys(function($field) {

            try {
                list($name, $type) = explode(':', $field);
            } catch (Exception $e) {
                list($name, $type) = ['', ''];
            }

            $this->getMediaMapper()->{$type}($name);

            return [$name => $this->determineMediaClass($type)];

        })->toArray();
    }

    protected function determineMediaClass($type)
    {
        switch ($type) {
            case 'image': return Image::class;
            case 'file': return File::class;
        }

        throw new InvalidArgumentException('Invalid media field type.');
    }

    public function mapMedia(MediaMapper $mapper) { }

}