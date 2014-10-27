<?php

namespace Rz\ContactBundle\SpamDetection;

/**
 * spam detector interface
 *
 * @author digitalkaoz <seroscho@googlemail.com>
 */
interface SpamDetectorInterface
{
    /**
     * checks if a message is spam
     * 
     * @return boolean
     */
    public function isSpam(array $data);
}

