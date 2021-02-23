<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataSourceBundle\DataSource\Extension\Symfony\DependencyInjection\Driver;

use FSi\Component\DataSource\Driver\DriverExtensionInterface;
use FSi\Component\DataSource\Field\FieldExtensionInterface;
use FSi\Component\DataSource\Field\FieldTypeInterface;
use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DriverExtension implements DriverExtensionInterface
{
    /**
     * @var string
     */
    private $driverType;

    /**
     * @var array<FieldTypeInterface>
     */
    private $fieldTypes = [];

    /**
     * @var array<string, array<FieldExtensionInterface>>
     */
    private $fieldExtensions = [];

    /**
     * @var array<EventSubscriberInterface>
     */
    private $eventSubscribers = [];

    /**
     * @param string $driverType
     * @param array<FieldTypeInterface> $fieldTypes
     * @param array<FieldExtensionInterface> $fieldExtensions
     * @param array<EventSubscriberInterface> $eventSubscribers
     */
    public function __construct(string $driverType, array $fieldTypes, array $fieldExtensions, array $eventSubscribers)
    {
        $this->driverType = $driverType;

        foreach ($fieldTypes as $fieldType) {
            $this->fieldTypes[$fieldType->getType()] = $fieldType;
        }

        foreach ($fieldExtensions as $fieldExtension) {
            foreach ($fieldExtension->getExtendedFieldTypes() as $extendedFieldType) {
                if (false === array_key_exists($extendedFieldType, $this->fieldExtensions)) {
                    $this->fieldExtensions[$extendedFieldType] = [];
                }

                $this->fieldExtensions[$extendedFieldType][] = $fieldExtension;
            }
        }

        $this->eventSubscribers = $eventSubscribers;
    }

    public function getExtendedDriverTypes(): array
    {
        return [$this->driverType];
    }

    public function hasFieldType(string $type): bool
    {
        return array_key_exists($type, $this->fieldTypes);
    }

    public function getFieldType(string $type): FieldTypeInterface
    {
        if (false === array_key_exists($type, $this->fieldTypes)) {
            throw new InvalidArgumentException(
                sprintf('The field type "%s" is not registered within the service container.', $type)
            );
        }

        return $this->fieldTypes[$type];
    }

    public function hasFieldTypeExtensions(string $type): bool
    {
        return array_key_exists($type, $this->fieldExtensions);
    }

    public function getFieldTypeExtensions(string $type): array
    {
        if (false === array_key_exists($type, $this->fieldExtensions)) {
            return [];
        }

        return $this->fieldExtensions[$type];
    }

    public function loadSubscribers(): array
    {
        return $this->eventSubscribers;
    }
}
