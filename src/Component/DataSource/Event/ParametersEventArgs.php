<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\DataSource\Event;

use FSi\Component\DataSource\DataSourceInterface;

abstract class ParametersEventArgs extends DataSourceEventArgs
{
    /**
     * @var mixed
     */
    private $parameters;

    /**
     * @param DataSourceInterface<mixed> $dataSource
     * @param mixed $parameters
     */
    public function __construct(DataSourceInterface $dataSource, $parameters)
    {
        parent::__construct($dataSource);

        $this->setParameters($parameters);
    }

    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param mixed $parameters
     */
    public function setParameters($parameters): void
    {
        $this->parameters = $parameters;
    }
}
