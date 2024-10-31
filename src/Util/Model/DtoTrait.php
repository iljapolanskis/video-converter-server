<?php

namespace App\Util\Model;

use ReflectionClass;

trait DtoTrait
{
    public function __toString(): string
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties();
        $data = [];

        foreach ($properties as $property) {
            if ($property->isPublic()) {
                $data[$property->getName()] = $property->getValue($this);
            }
        }

        return json_encode($data);
    }
}
