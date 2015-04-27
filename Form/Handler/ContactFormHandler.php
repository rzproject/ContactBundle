<?php

/**
 * (c) Mell M. Zamora <rzproject.org> and creadits to the original author Antoine Berranger <antoine@ihqs.net>
 * (c) Laszlo Horvath <pentarim@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */


namespace Rz\ContactBundle\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;

use Rz\ContactBundle\Model\ContactInterface;
use Sonata\CoreBundle\Model\ManagerInterface;
use Rz\ContactBundle\Event;
use Rz\ContactBundle\Event\Events;
use Rz\ContactBundle\Form\Extension\Spam\Provider\TimedSpamProviderInterface;

class ContactFormHandler
{
    protected $form;
    protected $request;
    protected $contactManager;
    protected $eventDispatcher;
    protected $timeProvider;

    public function __construct(Form $form, Request $request, ManagerInterface $contactManager, EventDispatcherInterface $eventDispatcher, TimedSpamProviderInterface $timeProvider)
    {
        $this->form            = $form;
        $this->request         = $request;
        $this->contactManager  = $contactManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->timeProvider = $timeProvider;
    }

    public function process(ContactInterface $contact)
    {
        if (null === $contact) {
            $contact = $this->contactManager->createContact();
        }

        $this->form->setData($contact);

        //TODO: put it on config & dependecy injection
        $options = array('min' => 10, 'max' => 3600, 'message' => 'Opps you submited the form too quickly.');

        if ('POST' == $this->request->getMethod()) {
            $this->form->handleRequest($this->request);
            if ($this->form->isValid()) {
                if($this->timeProvider->isFormTimeValid($this->form->getName(), $options)) {
                    if($this->contactManager->findOneBy(array('senderEmail'=>$contact->getSenderEmail(),
                                                              'subject'=>$contact->getSubject(),
                                                              'senderName'=>$contact->getSenderName()
                                                             ))) {

                        $this->form->addError(new FormError('message.contact_us.error.invalid_spam_detected'));
                        return false;
                    } else {
                        $this->onSuccess($contact);
                        return true;
                    }
                } else {
                    $this->form->addError(new FormError('message.contact_us.error.invalid_spam_too_fast'));
                }
            }
        }

        return false;
    }

    protected function onSuccess(ContactInterface $contact)
    {
        if($this->timeProvider->hasFormTime($this->form->getName())) {
            $this->timeProvider->removeFormTime($this->form->getName());
        }

        $this->eventDispatcher->dispatch(Events::onContactRequest, new Event\ContactEvent($contact));
    }
}
