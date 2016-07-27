<?php

namespace AlecGunnar\Data\Mapper;

use PHPUnit_Framework_TestCase;
use AlecGunnar\Data\Mapper\Mapping\AbstractMapping;

class TestClass
{
    public function setData() { }
}

class AbstractMapperTest extends PHPUnit_Framework_TestCase
{
    public function testMapCallsMapToMethods()
    {
        $field = 'testField';
        $method = 'setData';
        $given = $expected = 'data value';

        $object = $this->getMockBuilder(TestClass::class)
            ->getMock();

        $object->expects($this->once())
            ->method('setData')
            ->with($expected);

        $instance = $this->getAbstractMapperInstance(TestClass::class, [
            $field => [
                AbstractMapping::MAPS_VIA => AbstractMapping::MAPS_VIA_METHOD,
                AbstractMapping::MAPS_NAME => $method
            ]
        ]);

        $instance->map([
            $field => $given
        ], $object);
    }

    public function testMapCallsSetsToProperty()
    {
        $field = 'testField';
        $property = 'data';
        $given = $expected = 'data value';

        $object = new TestClass();

        $instance = $this->getAbstractMapperInstance(TestClass::class, [
            $field => [
                AbstractMapping::MAPS_VIA => AbstractMapping::MAPS_VIA_PROPERTY,
                AbstractMapping::MAPS_NAME => $property
            ]
        ]);

        $instance->map([
            $field => $given
        ], $object);

        $this->assertAttributeEquals($expected, $property, $object);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionThrownWhenWrongTypeIsProvided()
    {
        $instance = $this->getAbstractMapperInstance(TestClass::class, []);
        $instance->map([], new \stdClass());
    }

    protected function getAbstractMapperInstance(string $type, array $mappings)
    {
        $mapping = new class($type, $mappings) extends AbstractMapping {
            protected $type;
            protected $maps;

            public function __construct(string $type, array $maps)
            {
                $this->type = $type;
                $this->maps = $maps;

                parent::__construct();
            }

            public function getType()
            {
                return $this->type;
            }

            protected function buildMappings()
            {
                foreach ($this->maps as $field => $data) {
                    switch ($data[AbstractMapping::MAPS_VIA]) {
                        case AbstractMapping::MAPS_VIA_METHOD:
                            $this->mapToMethod($field, $data[AbstractMapping::MAPS_NAME]);
                            break;
                        case AbstractMapping::MAPS_VIA_PROPERTY:
                            $this->mapToProperty($field, $data[AbstractMapping::MAPS_NAME]);
                            break;
                    }
                }
            }
        };

        $instance = new class($mapping) extends AbstractMapper {
            protected $mapping;

            public function __construct(AbstractMapping $mapping)
            {
                $this->mapping = $mapping;
            }

            protected function getMapping(): AbstractMapping
            {
                return $this->mapping;
            }
        };

        return $instance;
    }
}
