<?php

namespace Webeleven\EasyMedia\Mapping;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;

class MediaMapper
{

    protected $mappings;

    protected $baseUploadDir;

    private $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
        $this->mappings = new Collection;
    }

    public function file($name)
    {
        $mapping = new FileMapping($this);

        $mapping->setKey($name);

        $this->mappings->put($name, $mapping);

        return $mapping;
    }

    public function image($name)
    {
        $mapping = new ImageMapping($this);

        $mapping->setKey($name);

        $this->mappings->put($name, $mapping);

        return $mapping;
    }

    public function getMappings()
    {
        return $this->mappings;
    }

    public function findMapping($key)
    {
        return $this->mappings->get($key);
    }

    public function baseUploadDir($dirName)
    {
        if (Str::endsWith($dirName, '/')) {
            $dirName = substr($dirName, 0, -1);
        }

        $this->baseUploadDir = $dirName;

        return $this;
    }

    public function getBaseUploadDir()
    {
        if ($this->baseUploadDir !== null) {

            preg_match('/\{(.*)\}/', $this->baseUploadDir, $matches);

            if (count($matches)) {
                $field = $matches[1];
                return preg_replace('/\{' . $field . '\}/', $this->entity->{$field}, $this->baseUploadDir);
            }

            return $this->baseUploadDir;
        }

        $reflect = new ReflectionClass($this->entity);

        $dir = ! empty($this->entity->getKey()) ? $this->entity->getKey() : md5(uniqid());

        return $this->baseUploadDir = Str::snake(Str::lower($reflect->getShortName())) . '/' . $dir;
    }

    public function getEntity()
    {
        return $this->entity;
    }

}