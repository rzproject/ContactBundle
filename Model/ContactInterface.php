<?php

/**
 * (c) Mell M. Zamora <rzproject.org> and creadits to the original author Antoine Berranger <antoine@ihqs.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rz\ContactBundle\Model;

interface ContactInterface
{
    public function setSenderEmail($senderEmail);

    public function getSenderEmail();

    public function setSenderName($senderName);

    public function getSenderName();

    public function setSubject($subject);

    public function getSubject();

    public function setMessage($message);

    public function getMessage();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function setUpdatedAt($updatedAt);
}
