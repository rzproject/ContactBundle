<?php

/**
 * (c) Mell M. Zamora <rzproject.org> and creadits to the original author Antoine Berranger <antoine@ihqs.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rz\ContactBundle\Connector;

use Rz\ContactBundle\Model\ContactInterface;
use Rz\ContactBundle\Event\ContactEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

interface ConnectorInterface
{
    //function register(EventDispatcher $dispatcher, $priority = 0);

    function doProcess(ContactInterface $contact);

    function onContactRequest(ContactEvent $contactEvent);
}