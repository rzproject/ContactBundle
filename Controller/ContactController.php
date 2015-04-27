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
use Symfony\Component\HttpFoundation\JsonResponse;

class ContactController extends Controller
{
    public function formAction($method = 'GET')
    {
        $contact = $this->get('rz_contact.contact_manager')->createContact();

        $form        = $this->get('rz_contact.form.contact');
        $formHandler = $this->get('rz_contact.form.contact.handler');

        $formView = $this->container->getParameter('rz_contact.form.contact.view');
        $formView = ($formView) ? $formView : 'RzContactBundle:Contact:form.html.twig';

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

    public function submitAction(){
        $request = $this->get('request_stack')->getCurrentRequest();

        // redirect to page if not ajax request
        if (!$request->isXmlHttpRequest()) {
            //TODO add redirect URL to configuration
            return $this->redirect($this->generateUrl('application_rz_contact_us'));
        }

        $contact = $this->get('rz_contact.contact_manager')->createContact();
        $form = $this->get('rz_contact.form.contact');


        $timeProvider = $this->get('rz_contact.form.extension.provider.timed_spam');

        if (!$timeProvider->hasFormTime($form->getName())) {
            $timeProvider->generateFormTime($form->getName());
        }

        $formHandler = $this->get('rz_contact.form.contact.handler');

        if ($formHandler->process($contact)) {
            if ('json' == $request->get('_format')) {
                $message = $this->get('translator')->trans('message.contact_us.success',array(),'RzContactBundle');
                return new JsonResponse(array('message' => $message));
            } else {
                $message = $this->get('translator')->trans('message.contact_us.error.invalid_format',array(),'RzContactBundle');
                $response = new JsonResponse(array('message' => $message, 'messages'=>$this->serializeFormErrors($form, true, true)));
                $response->setStatusCode(419);
                return $response;
            }
        } else {
            $message = $this->get('translator')->trans('message.contact_us.error.invalid_form',array(),'RzContactBundle');
            $response = new JsonResponse(array('message' => $message, 'messages'=>$this->serializeFormErrors($form, true, true)));
            $response->setStatusCode(419);
            return $response;
        }
    }

    public function serializeFormErrors(\Symfony\Component\Form\Form $form, $flat_array = false, $add_form_name = false, $glue_keys = '_')
    {
        $errors = array();
        $errors['global'] = array();
        $errors['fields'] = array();

        foreach ($form->getErrors() as $error) {
            $errors['global'][] = $this->get('translator')->trans($error->getMessage(),array(),'RzContactBundle');
        }

        $errors['fields'] = $this->serialize($form);

        if ($flat_array) {
            $errors['fields'] = $this->arrayFlatten($errors['fields'],
                $glue_keys, (($add_form_name) ? $form->getName() : ''));
        }


        return $errors;
    }

    private function serialize(\Symfony\Component\Form\Form $form)
    {
        $local_errors = array();
        foreach ($form->getIterator() as $key => $child) {

            foreach ($child->getErrors() as $error){
                $local_errors[$key] = $error->getMessage();
            }

            if (count($child->getIterator()) > 0) {
                $local_errors[$key] = $this->serialize($child);
            }
        }

        return $local_errors;
    }

    private function arrayFlatten($array, $separator = "_", $flattened_key = '') {
        $flattenedArray = array();
        foreach ($array as $key => $value) {

            if(is_array($value)) {

                $flattenedArray = array_merge($flattenedArray,
                    $this->arrayFlatten($value, $separator,
                        (strlen($flattened_key) > 0 ? $flattened_key . $separator : "") . $key)
                );

            } else {
                $flattenedArray[(strlen($flattened_key) > 0 ? $flattened_key . $separator : "") . $key] = $value;
            }
        }
        return $flattenedArray;
    }
}