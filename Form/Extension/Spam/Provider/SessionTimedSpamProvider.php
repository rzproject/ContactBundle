<?php

namespace Rz\ContactBundle\Form\Extension\Spam\Provider;

use Symfony\Component\HttpFoundation\Session\Session;

class SessionTimedSpamProvider implements TimedSpamProviderInterface
{
    protected $session;
    protected $minTime;
    protected $maxTime;

    public function __construct(Session $session,
                                $minTime = 10,
                                $maxTime = 3600)
    {
        $this->session = $session;
        $this->minTime = $minTime;
        $this->maxTime = $maxTime;
    }

    public function generateFormTime($name)
    {
        $startTime = new \DateTime();
        $key = $this->getSessionKey($name);

        $this->session->set($key, $startTime);

        return $startTime;
    }

    public function isFormTimeValid($name)
    {
        $valid = true;
        $startTime = $this->getFormTime($name);
        /*
         * No value stored, so this can't be valid or session expired.
         */
        if($startTime === false){
            return false;
        }

        $currentTime = new \DateTime();


        /** @var TYPE_NAME $startTime */
        $minTime = clone $startTime;
        $minTime->modify(sprintf('+%d seconds', $this->minTime));
        if ($minTime > $currentTime) {
            $valid = false;
        };

        if( $valid){
            /** @var TYPE_NAME $startTime */
            $maxTime = clone $startTime;
            $maxTime->modify(sprintf('+%d seconds', $this->maxTime));
            if($maxTime < $currentTime) {
                $valid = true;
            }
        }

        return $valid;
    }

    public function hasFormTime($name)
    {
        $key = $this->getSessionKey($name);

        return $this->session->has($key);
    }

    public function getFormTime($name)
    {
        $key = $this->getSessionKey($name);

        if($this->hasFormTime($name)){
            return $this->session->get($key);
        }

        return false;
    }

    public function removeFormTime($name)
    {
        $key = $this->getSessionKey($name);

        $this->session->remove($key);
    }

    public function getSessionKey($name)
    {
        return 'rz-contact-timedSpam/'.$name;
    }
}