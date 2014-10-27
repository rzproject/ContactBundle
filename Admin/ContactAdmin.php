<?php


namespace Rz\ContactBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ContactAdmin extends Admin
{
    protected $formOptions = array(
        'cascade_validation' => true
    );

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('senderEmail', null, array('required' => true))
            ->add('senderName', null, array('required' => true))
            ->add('subject', null, array('required' => true))
            ->add('message', 'textarea', array('required' => true))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('senderEmail')
            ->add('senderName')
            ->add('subject')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('senderEmail')
            ->add('senderName')
            ->add('subject')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('senderEmail', null, array('footable'=>array('attr'=>array('data_toggle'=>true))))
            ->add('senderName', null, array('footable'=>array('attr'=>array('data_hide'=>'phone'))))
            ->add('subject', null, array('editable' => true, 'footable'=>array('attr'=>array('data_hide'=>'phone'))))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'Show' => array('template' => 'SonataAdminBundle:CRUD:list__action_show.html.twig'),
                    'Edit' => array('template' => 'SonataAdminBundle:CRUD:list__action_edit.html.twig'),
                    'Delete' => array('template' => 'SonataAdminBundle:CRUD:list__action_delete.html.twig'),
                ),
                'footable'=>array('attr'=>array('data_hide'=>'phone,tablet'))
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getListModes()
    {
        return parent::getListMode();
    }

    /**
     * {@inheritdoc}
     */
    public function setListMode($mode)
    {
        return parent::setListMode($mode);
    }

    /**
     * {@inheritdoc}
     */
    public function getListMode()
    {
        return parent::getListMode();
    }
}
