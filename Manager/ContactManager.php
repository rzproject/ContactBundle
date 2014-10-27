<?php

/**
 * (c) Mell M. Zamora <rzproject.org> and creadits to the original author Antoine Berranger <antoine@ihqs.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rz\ContactBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class ContactManager extends BaseManager implements ContactManagerInterface
{
    public function __construct($class)
    {
        $this->class = $class;
    }

    public function createContact()
    {
        $class = $this->getClass();

        return new $class();
    }

    public function getClass()
    {
        return $this->class;
    }
}