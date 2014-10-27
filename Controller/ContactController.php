<?php

/**
 * (c) Mell M. Zamora <rzproject.org> and creadits to the original author Antoine Berranger <antoine@ihqs.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rz\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ContactController extends Controller
{
    public function formAction($method = 'GET')
    {
        $contact = $this->get('rz_contact.contact_manager')->createContact();

        $form        = $this->get('rz_contact.contact.form');
        $formHandler = $this->get('rz_contact.contact.form.handler');

        $formView = $this->container->getParameter('rz_contact.contact.form.view');
        $formView = ($formView) ? $formView : 'IHQSContactBundle:Contact:form.html.twig';

        if ($method == 'POST') {
            if ($formHandler->process($contact)) {
                return new Response($this->get('translator')->trans('Message sent'));
            }
        }

        return $this->render($formView, array(
                'form' => $form->createView(),
            )
        );
    }
}