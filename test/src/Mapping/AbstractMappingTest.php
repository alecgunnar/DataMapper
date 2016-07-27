<?php

namespace AlecGunnar\Data\Mapper\Mapping;

use PHPUnit_Framework_TestCase;

class AbstractMappingTest extends PHPUnit_Framework_TestCase
{
    public function testMapToMethodAddsMap()
    {
        $from = 'source_data';
        $name = 'receive_method';

        $expected = [
            $from => [
                AbstractMapping::MAPS_VIA => AbstractMapping::MAPS_VIA_METHOD,
                AbstractMapping::MAPS_NAME => $name
            ]
        ];

        $instance = new class($from, $name) extends AbstractMapping {
            public function __construct($from, $name)
            {
                $this->from = $from;
                $this->name = $name;

                parent::__construct();
            }

            public function getType()
            {
                return 'type';
            }

            protected function buildMappings()
            {
                $this->mapToMethod($this->from, $this->name);
            }
        };

        $this->assertAttributeEquals($expected, 'mappings', $instance);
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testMapToMethodThrowsExceptionWhenConstructorUsed()
    {
        $instance = new class extends AbstractMapping {
            public function getType()
            {
                return 'type';
            }

            protected function buildMappings()
            {
                $this->mapToMethod('field', '__construct');
            }
        };
    }

    public function testMapToPropertyAddsMap()
    {
        $from = 'source_data';
        $name = 'receive_property';

        $expected = [
            $from => [
                AbstractMapping::MAPS_VIA => AbstractMapping::MAPS_VIA_PROPERTY,
                AbstractMapping::MAPS_NAME => $name
            ]
        ];

        $instance = new class($from, $name) extends AbstractMapping {
            public function __construct($from, $name)
            {
                $this->from = $from;
                $this->name = $name;

                parent::__construct();
            }

            public function getType()
            {
                return 'type';
            }

            protected function buildMappings()
            {
                $this->mapToProperty($this->from, $this->name);
            }
        };

        $this->assertAttributeEquals($expected, 'mappings', $instance);
    }

    public function testGetMappingsReturnsBuiltMappings()
    {
        $from = 'source_data';
        $name = 'receive_property';

        $given = $expected = [
            $from => [
                AbstractMapping::MAPS_VIA => AbstractMapping::MAPS_VIA_PROPERTY,
                AbstractMapping::MAPS_NAME => $name
            ]
        ];

        $instance = new class($from, $name) extends AbstractMapping {
            public function __construct($from, $name)
            {
                $this->from = $from;
                $this->name = $name;

                parent::__construct();
            }

            public function getType()
            {
                return 'type';
            }

            protected function buildMappings()
            {
                $this->mapToProperty($this->from, $this->name);
            }
        };

        $this->assertEquals($expected, $instance->getMappings());
    }

    public function testSerializeReturnsSerializedMappingsString()
    {
        $instance = new class extends AbstractMapping {
            public function getType()
            {
                return 'type';
            }

            protected function buildMappings()
            {
                $this->mapToProperty('field', 'name');
            }
        };

        $type = $instance->getType();
        $mappings = $instance->getMappings();

        $expected = json_encode([
            $type,
            $mappings
        ]);

        $ret = $instance->serialize();

        $this->assertEquals($expected, $ret);
    }

    public function testUnserializeAddsMappingsToObject()
    {
        $expected = new class extends AbstractMapping {
            public function getType()
            {
                return 'type';
            }

            protected function buildMappings()
            {
                $this->mapToProperty('expected_field', 'name');
            }
        };

        $instance = new class extends AbstractMapping {
            public function getType()
            {
                return 'type';
            }

            protected function buildMappings()
            {
                // Should be overwritten
                $this->mapToProperty('instance_field', 'name');
            }
        };

        $instance->unserialize($expected->serialize());

        $this->assertEquals($expected->getMappings(), $instance->getMappings());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Cannot unserialize mappings from "given_type" to "some_other_type".
     */
    public function testUnserializeThrowsExceptionWhenTypeMismatchOccurs()
    {
        $expected = new class extends AbstractMapping {
            public function getType()
            {
                return 'given_type';
            }

            protected function buildMappings() { }
        };

        $instance = new class extends AbstractMapping {
            public function getType()
            {
                return 'some_other_type';
            }

            protected function buildMappings() { }
        };

        $instance->unserialize($expected->serialize());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Cannot unserialize, the data is invalid.
     */
    public function testUnserializeThrowsExceptionWhenDataIsChanged()
    {
        $instance = new class extends AbstractMapping {
            public function getType()
            {
                return 'some_other_type';
            }

            protected function buildMappings() { }
        };

        $instance->unserialize(json_encode([
            $instance->getType()
        ]));
    }
}
