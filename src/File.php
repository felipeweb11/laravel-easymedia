<?php

namespace Webeleven\EasyMedia;

use Webeleven\EasyMedia\Common\ValueObject;

class File implements ValueObject
{

    protected $name;

    protected $extension;

    protected $size;

    protected $path;

    protected $mimeType;

    public function __construct($value = null)
    {
        if (! is_array($value)) {
            $value = json_decode($value, true);
        }

        if ($value !== null) {
            $this->setData($value);
        }
    }

    protected function setData(array $data)
    {
        $this->name = array_get($data, 'name');
        $this->size = array_get($data, 'size');
        $this->extension = array_get($data, 'extension');
        $this->path = array_get($data, 'path');
        $this->mimeType = array_get($data, 'mimeType');
    }

    /**
     * @return mixed
     */
    public function toScalar()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'path' => $this->path,
            'size' => $this->size,
            'extension' => $this->extension,
            'mimeType' => $this->mimeType
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }

    public function __set($key, $value)
    {
        $this->$key = $value;
    }

    public function __get($key)
    {
        if (method_exists($this, $key)) {
            return call_user_func([$this, $key]);
        }

        return $this->$key;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function filename()
    {
        return $this->name . '.' . $this->extension;
    }

    public function path()
    {
        return $this->path;
    }

    public function url()
    {
        return $this->getStorageDisk()->url($this->path());
    }

    protected function getStorageDisk()
    {
        return app('filesystem')->disk(config('easymedia.storage_disk'));
    }

    public function download($name = null)
    {
        $file = $this->getStorageDisk()->get($this->path());

        $downloadName = ! empty($name) ? $name : $this->name;

        $downloadName .= sprintf('.%s', $this->extension);

        return response($file)
            ->header('Content-Type', $this->getMimeType())
            ->header('Content-Disposition', 'attachment; filename="' . $downloadName . '"');
    }

    public function downloadLink()
    {
        return $this->url();
    }
}