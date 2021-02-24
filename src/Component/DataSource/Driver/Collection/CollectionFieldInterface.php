<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Component\DataSource\Driver\Collection;

use Doctrine\Common\Collections\Criteria;
use FSi\Component\DataSource\Field\FieldTypeInterface;

interface CollectionFieldInterface extends FieldTypeInterface
{
    public function buildCriteria(Criteria $c): void;

    public function getPHPType(): ?string;
}
