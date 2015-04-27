<?php

namespace Rz\ContactBundle\Document;

use Doctrine\ORM\DocumentManager;
use Sonata\CoreBundle\Model\BaseDocumentManager;

class ContactManager extends BaseDocumentManager
{
    protected $dm;
    protected $class;
    protected $repository;

    public function __construct(DocumentManager $dm, $class)
    {
        $this->dm = $dm;
        $this->repository  = $dm->getRepository($class);

        $metadata = $dm->getClassMetadata($class);
        $this->class = $metadata->name;
    }
}