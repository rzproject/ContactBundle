<?php

namespace Rz\ContactBundle\Entity;

use Rz\ContactBundle\Model\Contact;

class BaseContact extends Contact
{
    public function prePersist()
    {
        $this->setCreatedAt(new \DateTime);
        $this->setUpdatedAt(new \DateTime);
    }

    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime);
    }
}