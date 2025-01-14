<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\DataGrid;

use FSi\Component\DataGrid\Column\ColumnInterface;
use FSi\Component\DataGrid\Data\DataRowset;
use FSi\Component\DataGrid\Data\DataRowsetInterface;
use FSi\Component\DataGrid\Event\PostBuildViewEvent;
use FSi\Component\DataGrid\Event\PostSetDataEvent;
use FSi\Component\DataGrid\Event\PreBuildViewEvent;
use FSi\Component\DataGrid\Event\PreSetDataEvent;
use FSi\Component\DataGrid\Exception\DataGridException;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReturnTypeWillChange;
use RuntimeException;

use function array_key_exists;
use function count;
use function current;
use function key;
use function next;
use function reset;
use function sprintf;

final class DataGrid implements DataGridInterface
{
    private DataGridFactoryInterface $dataGridFactory;
    private EventDispatcherInterface $eventDispatcher;
    private string $name;
    private ?DataRowsetInterface $rowset;
    /**
     * @var array<string,ColumnInterface>
     */
    private array $columns;

    public function __construct(
        DataGridFactoryInterface $dataGridFactory,
        EventDispatcherInterface $eventDispatcher,
        string $name
    ) {
        $this->dataGridFactory = $dataGridFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->name = $name;
        $this->rowset = null;
        $this->columns = [];
    }

    public function getFactory(): DataGridFactoryInterface
    {
        return $this->dataGridFactory;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addColumn(string $name, string $type, array $options = []): DataGridInterface
    {
        $columnType = $this->dataGridFactory->getColumnType($type);
        return $this->addColumnInstance($columnType->createColumn($this, $name, $options));
    }

    public function addColumnInstance(ColumnInterface $column): DataGridInterface
    {
        if ($column->getDataGrid() !== $this) {
            throw new InvalidArgumentException(sprintf(
                'Tried to add column "%s" associated with datagrid "%s" to datagrid "%s"',
                $column->getName(),
                $column->getDataGrid()->getName(),
                $this->name
            ));
        }

        $this->columns[$column->getName()] = $column;
        return $this;
    }

    public function removeColumn(string $name): DataGridInterface
    {
        if (false === $this->hasColumn($name)) {
            throw new InvalidArgumentException(
                "Column \"{$name}\" does not exist in datagrid \"{$this->name}\"."
            );
        }

        unset($this->columns[$name]);

        return $this;
    }

    public function clearColumns(): void
    {
        $this->columns = [];
    }

    public function getColumn(string $name): ColumnInterface
    {
        if (false === $this->hasColumn($name)) {
            throw new InvalidArgumentException("Column \"{$name}\" does not exist in data grid.");
        }

        return $this->columns[$name];
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function hasColumn(string $name): bool
    {
        return array_key_exists($name, $this->columns);
    }

    public function hasColumnType(string $type): bool
    {
        foreach ($this->columns as $column) {
            if ($column->getType()->getId() === $type) {
                return true;
            }
        }

        return false;
    }

    public function createView(): DataGridViewInterface
    {
        $this->eventDispatcher->dispatch(new PreBuildViewEvent($this));

        $view = new DataGridView($this->name, $this->columns, $this->getRowset());

        $this->eventDispatcher->dispatch(new PostBuildViewEvent($this, $view));

        return $view;
    }

    public function setData(iterable $data): void
    {
        $event = new PreSetDataEvent($this, $data);
        $this->eventDispatcher->dispatch($event);
        $this->rowset = new DataRowset($event->getData());
        $this->eventDispatcher->dispatch(new PostSetDataEvent($this, $this->rowset));
    }

    public function count(): int
    {
        if (null === $this->rowset) {
            throw $this->createUninitializedDataException();
        }

        return $this->rowset->count();
    }

    /**
     * @return array<string,mixed>|object|false
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        if (null === $this->rowset) {
            throw $this->createUninitializedDataException();
        }

        return $this->rowset->current();
    }

    /**
     * @return int|string|null
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        if (null === $this->rowset) {
            throw $this->createUninitializedDataException();
        }

        return $this->rowset->key();
    }

    public function next(): void
    {
        if (null === $this->rowset) {
            throw $this->createUninitializedDataException();
        }

        $this->rowset->next();
    }

    public function rewind(): void
    {
        if (null === $this->rowset) {
            throw $this->createUninitializedDataException();
        }

        $this->rowset->rewind();
    }

    public function valid(): bool
    {
        if (null === $this->rowset) {
            throw $this->createUninitializedDataException();
        }

        return $this->rowset->valid();
    }

    public function offsetExists($offset): bool
    {
        if (null === $this->rowset) {
            throw $this->createUninitializedDataException();
        }

        return $this->rowset->offsetExists($offset);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (null === $this->rowset) {
            throw $this->createUninitializedDataException();
        }

        return $this->rowset->offsetGet($offset);
    }

    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('Method not implemented');
    }

    public function offsetUnset($offset): void
    {
        throw new RuntimeException('Method not implemented');
    }

    private function getRowset(): DataRowsetInterface
    {
        if (null === $this->rowset) {
            throw $this->createUninitializedDataException();
        }

        return $this->rowset;
    }

    private function createUninitializedDataException(): DataGridException
    {
        return new DataGridException(
            "DataGrid \"{$this->name}\" has not been initialized with data."
        );
    }
}
