<?php


namespace Rz\ContactBundle\Block;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContactUsFormBlockService extends BaseBlockService
{
    protected $container;

    /**
     * @param string $name
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     *
     */
    public function __construct($name, EngineInterface $templating, ContainerInterface $container)
    {
        parent::__construct($name, $templating);
        $this->container    = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Ask The Expert User';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'title'   => false,
            'template' =>  $this->container->getParameter('rz_contact.block.contact.contact_us_form.default_template')
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('title', 'text', array('required' => false, 'label'=> 'Title')),
                array('template', 'choice', array('required' => true, 'choices' => $this->container->getParameter('rz_contact.block.contact.contact_us_form.template_choices'))),
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {

        $form = $this->container->get('rz_contact.form.contact');
        // generate time for spam detection
        $timeProvider = $this->container->get('rz_contact.form.extension.provider.timed_spam');
        $timeProvider->generateFormTime($form->getName());

        return $this->renderResponse($blockContext->getTemplate(), array(
            'block'     => $blockContext->getBlock(),
            'settings'  => $blockContext->getSettings(),
            'form'      => $form->createView()
        ), $response);
    }


    /**
     * @param ErrorElement   $errorElement
     * @param BlockInterface $block
     *
     * @return void
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block){

    }

//    /**
//     * {@inheritdoc}
//     */
//    public function getStylesheets($media)
//    {
//        return array(
//            '/bundles/rzcontact/css/main_override.css'
//        );
//    }

    /**
     * {@inheritdoc}
     */
    public function getJavascripts($media)
    {
        return array(
            '/bundles/rmzamorajquery/jquery-plugins/validation/dist/jquery.validate.js',
            '/bundles/rzcontact/js/contact-form.js',
        );
    }
}
