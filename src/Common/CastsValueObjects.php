<?php

namespace Webeleven\EasyMedia\Common;

trait CastsValueObjects
{
    /**
     * @var array
     */
    protected $cachedObjects = [];

    public static function bootCastsValueObjects()
    {
        static::saving(function($model) {

            if ($objects = $model->getValueObjects()) {
                foreach ($objects as $key => $value) {
                    if (isset($model->{$key})) {
                        $model->attributes[$key] = $model->{$key}->__tostring();
                    }
                }
            }

        });
    }

    public function getValueObjects()
    {
        return isset($this->objects) && is_array($this->objects) ? $this->objects : null;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        $objects = $this->getValueObjects();

        if (isset($objects[$key])) {

            // Allow other mutators and such to do their work first.
            $value = parent::getAttribute($key);

            // Don't cast empty $value.
            if ($value === null || $value === '') {
                return null;
            }

            // Cache the instantiated value for future access.
            // This allows tests such as ($model->casted === $model->casted) to be true.
            if (! $this->isValueObjectCached($key)) {
                $this->cacheValueObject(
                    $key,
                    $this->createValueObject($key, $value)
                );
            }

            return $this->getCachedValueObject($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute($key, $value)
    {
        if ($value instanceof ValueObject) {
            // The value provided is a value object. We'll need to cast it to a scalar
            // and then let it be set into Eloquent's attributes array.
            $scalar = $value->__toString();
            parent::setAttribute($key, $scalar);

            // Housekeeping.
            if ($this->attributes[$key] === $scalar) {
                // If the value wasn't modified during the set process
                // store the original ValueObject in our cache.
                $this->cacheValueObject($key, $value);
            } else {
                // Otherwise, we'll invalidate the cache for this key and defer
                // to the get action for re-instantiating the ValueObject.
                $this->invalidateValueObjectCache($key);
            }
        } elseif ($this->isValueObjectCached($key)) {
            // This means that an attribute that has been cached to a ValueObject is being
            // set directly as a scalar. We'll invalidate the cached ValueObject and defer
            // to the get action for re-instantiating the ValueObject.
            $this->invalidateValueObjectCache($key);
            parent::setAttribute($key, $value);
        } else {
            // Standard value given. No need to do anything special.
            parent::setAttribute($key, $value);
        }
    }

    /**
     * @param string $key
     * @param $value
     *
     * @return mixed
     */
    protected function createValueObject($key, $value)
    {
        $class = $this->getValueObjects()[$key];

        return new $class($value);
    }

    /**
     * @param string $key
     * @param ValueObject $object
     */
    private function cacheValueObject($key, ValueObject $object)
    {
        $this->cachedObjects[$key] = $object;
    }

    /**
     * @param string $key
     */
    private function invalidateValueObjectCache($key)
    {
        unset($this->cachedObjects[$key]);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    private function isValueObjectCached($key)
    {
        return isset($this->cachedObjects[$key]);
    }

    /**
     * @param string $key
     *
     * @return ValueObject
     */
    private function getCachedValueObject($key)
    {
        return $this->cachedObjects[$key];
    }

}