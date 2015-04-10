<?php

/**
 * (c) Mell M. Zamora <rzproject.org> and creadits to the original author Antoine Berranger <antoine@ihqs.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rz\ContactBundle\Connector;

use Rz\ContactBundle\Model\ContactInterface;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;

class Email extends BaseConnector implements ConnectorInterface
{
    public function doProcess(ContactInterface $contact)
    {
        $mailer = $this->container->get('mailer');

        $body = $this->container->get('templating')->render('RzContactBundle:Connector:email.html.twig', array('contact'=>$contact));

        $mail = \Swift_Message::newInstance()
            ->setSubject('Mosaic Platform | ' . $contact->getSubject())
            ->setFrom($contact->getSenderEmail(), $contact->getSenderName())
            ->setReplyTo($contact->getSenderEmail(), $contact->getSenderName())
            ->setTo($this->container->getParameter('rz_contact.email.recipients'))
            ->setBody($body)
            ->setContentType("text/html");

        $mailer->send($mail);

        return true;
    }
}

