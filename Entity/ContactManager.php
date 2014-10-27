<?php

namespace Rz\ContactBundle\Entity;

use Sonata\CoreBundle\Model\BaseEntityManager;

use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;

class ContactManager extends BaseEntityManager
{

    public function createContact()
    {
        $class = $this->getClass();

        return new $class();
    }

}