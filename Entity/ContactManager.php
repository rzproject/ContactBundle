<?php

namespace Rz\ContactBundle\Entity;

use Sonata\CoreBundle\Model\BaseEntityManager;

class ContactManager extends BaseEntityManager
{
    public function createContact()
    {
        $class = $this->getClass();

        return new $class();
    }

    public function findTimedDuplicate($senderEmail, $senderName, $contactNo, $noOfDaysToValidate)
    {
       $result = $this->getObjectManager()->createQuery(sprintf("SELECT c FROM %s c WHERE c.createdAt > :date and LOWER(c.senderEmail) = :senderEmail and LOWER(c.senderName) = :senderName and c.contactNo = :contactNo", $this->getClass()))
                                       ->setParameter('date', new \DateTime(sprintf('-%s day', $noOfDaysToValidate)))
                                       ->setParameter('senderEmail', strtolower($senderEmail))
                                       ->setParameter('senderName', strtolower($senderName))
                                       ->setParameter('contactNo', $contactNo)
                                       ->setMaxResults(1);
        return $result->execute();
    }

}