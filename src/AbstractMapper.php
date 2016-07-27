<?php
/**
 * Data Mapper
 *
 * @author Alec Carpenter <alecgunnar@gmail.com>
 */

namespace AlecGunnar\Data\Mapper;

use AlecGunnar\Data\Mapper\Mapping\AbstractMapping;
use InvalidArgumentException;

abstract class AbstractMapper
{
    /**
     * @var string
     */
    const WRONG_TYPE_EXCEPTION = 'Object must be of type %s, object of type %s given.';

    /**
     * Map the given data to the given object
     *
     * @param mixed[] $data
     * @param mived $object
     */
    final public function map(array $data, $object)
    {
        $mapping = $this->getMapping();
        $type = $mapping->getType();

        if (!($object instanceof $type)) {
            throw new InvalidArgumentException(
                sprintf(self::WRONG_TYPE_EXCEPTION, $mapping->getType(), get_class($object))
            );
        }

        foreach ($mapping->getMappings() as $field => $map) {
            if (array_key_exists($field, $data)) {
                $this->doMapping($data[$field], $map, $object);
            }
        }
    }

    /**
     * Return the mapping for this mapper
     *
     * @return AbstractMapping
     */
    abstract protected function getMapping(): AbstractMapping;

    private function doMapping(string $data, array $map, $object)
    {
        $name = $map[AbstractMapping::MAPS_NAME];

        switch ($map[AbstractMapping::MAPS_VIA]) {
            case AbstractMapping::MAPS_VIA_METHOD:
                $object->$name($data);
                break;

            case AbstractMapping::MAPS_VIA_PROPERTY:
                $object->$name = $data;
                break;
        }
    }
}
