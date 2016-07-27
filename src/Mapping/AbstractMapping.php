<?php
/**
 * Data Mapper
 *
 * @author Alec Carpenter <alecgunnar@gmail.com>
 */

namespace AlecGunnar\Data\Mapper\Mapping;

use Serializable;
use UnexpectedValueException;
use InvalidArgumentException;

abstract class AbstractMapping implements Serializable
{
    /**
     * @var string
     */
    const CANNOT_MAP_VIA_CONSTRUCTOR_EXCEPTION = 'Cannot map data via the constructor.';

    /**
     * @var string
     */
    const CANNOT_UNSERIALIZE_DATA_CHANGED_EXCEPTION = 'Cannot unserialize, the data is invalid.';

    /**
     * @var string
     */
    const CANNOT_UNSERIALIZE_TYPES_EXCEPTION = 'Cannot unserialize mappings from "%s" to "%s".';

    /**
     * @var string
     */
    const CONSTUCTOR_METHOD = '__construct';

    /**
     * @var string
     */
    const MAPS_NAME = 'name';

    /**
     * @var string
     */
    const MAPS_VIA = 'via';

    /**
     * @var string
     */
    const MAPS_VIA_METHOD = 'call';

    /**
     * @var string
     */
    const MAPS_VIA_PROPERTY = 'to';

    /**
     * The build mappings
     *
     * @var mixed[]
     */
    private $mappings = [];

    public function __construct()
    {
        $this->buildMappings();
    }

    /**
     * Return an associative array of data fields to object mappings
     *
     * @return mixed[]
     */
    final public function getMappings(): array
    {
        return $this->mappings;
    }

    final public function serialize(): string
    {
        return json_encode([
            $this->getType(),
            $this->mappings
        ]);
    }

    final public function unserialize($serialized)
    {
        $message = null;
        $data = json_decode($serialized, true);

        if (count($data) == 2) {
            list($type, $mappings) = $data;

            if ($type == $this->getType()) {
                return $this->mappings = $mappings;
            }

            $message = sprintf(self::CANNOT_UNSERIALIZE_TYPES_EXCEPTION, $type, $this->getType());
        }

        throw new InvalidArgumentException($message ?: self::CANNOT_UNSERIALIZE_DATA_CHANGED_EXCEPTION);
    }

    /**
     * Returns the type of object this mapping supports
     *
     * @return string
     */
    abstract public function getType();

    /**
     * Map a data field to a method on the object
     *
     * Does not support __construct
     *
     * @throws UnexpectedValueException
     * @param string $from
     * @param string $method
     */
    protected function mapToMethod(string $from, string $method)
    {
        if ($method == self::CONSTUCTOR_METHOD) {
            throw new UnexpectedValueException(self::CANNOT_MAP_VIA_CONSTRUCTOR_EXCEPTION);
        }

        $this->withMapping($from, self::MAPS_VIA_METHOD, $method);
    }

    /**
     * Map a data field to a method on the object
     *
     * @param string $from
     * @param string $method
     */
    protected function mapToProperty(string $from, string $method)
    {
        $this->withMapping($from, self::MAPS_VIA_PROPERTY, $method);
    }

    /**
     * Should call the various mapping helpers
     * provided by this abstract class to build
     * the mappings.
     */
    abstract protected function buildMappings();

    private function withMapping(string $field, string $via, string $name)
    {
        $this->mappings[$field] = [
            self::MAPS_VIA => $via,
            self::MAPS_NAME => $name
        ];
    }
}
