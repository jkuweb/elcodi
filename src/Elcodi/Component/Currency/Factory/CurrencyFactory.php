<?php

/**
 * This file is part of the Elcodi package.
 *
 * Copyright (c) 2014 Elcodi.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 * @author Aldo Chiecchia <zimage@tiscali.it>
 */

namespace Elcodi\Component\Currency\Factory;

use DateTime;

use Elcodi\Component\Core\Factory\Abstracts\AbstractFactory;
use Elcodi\Component\Currency\Entity\Currency;

/**
 * Class CurrencyFactory
 */
class CurrencyFactory extends AbstractFactory
{
    /**
     * Creates an instance of Currency entity
     *
     * @return Currency Empty entity
     */
    public function create()
    {
        /**
         * @var Currency $currency
         */
        $classNamespace = $this->getEntityNamespace();
        $currency = new $classNamespace();
        $currency
            ->setCreatedAt(new DateTime())
            ->setEnabled(true);

        return $currency;
    }
}