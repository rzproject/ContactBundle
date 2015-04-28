<?php

/**
 * (c) Mell M. Zamora <rzproject.org> and creadits to the original author Antoine Berranger <antoine@ihqs.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rz\ContactBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Config\Definition\Processor;

use Symfony\Component\Config\FileLocator;

class RzContactExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {

        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);
        $bundles = $container->getParameter('kernel.bundles');

        $loader = new XmlFileLoader($container, new FileLocator(array(__DIR__.'/../Resources/config')));

        if (isset($bundles['SonataAdminBundle']) && isset($bundles['RzAdminBundle'])) {
            $loader->load(sprintf('admin_%s.xml', $config['manager_type']));
        }

        $loader->load('model.xml');

        $config = $this->addDefaults($config);
        $this->configureAdminClass($config, $container);
        $this->configureClass($config, $container);
        $this->configureTranslationDomain($config, $container);
        $this->configureController($config, $container);
        $this->configureRzTemplates($config, $container);

        $loader->load('form.xml');
        $this->configureContactForm($config, $container);

        $loader->load('spam_detection.xml');
        $this->configureSpamDetection($config, $container);
        $this->configureConnectors($config, $container);

        $loader->load('block.xml');
        $this->configureBlocks($config, $container);

        // load connector configs
        foreach ($config['connectors'] as $connector => $attributes) {
            $loader->load("connector_$connector.xml");
        }

        $this->configureSettings($config, $container);
    }

    /**
     * @param array $config
     *
     * @return array
     */
    public function addDefaults(array $config)
    {
        if ('orm' === $config['manager_type']) {
            $modelType = 'Entity';
        } elseif ('mongodb' === $config['manager_type']) {
            $modelType = 'Document';
        }

        $defaultConfig['class']['contact']  = sprintf('Application\\Rz\\ContactBundle\\%s\\Contact', $modelType);

        return array_replace_recursive($defaultConfig, $config);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureClass($config, ContainerBuilder $container)
    {
        if ('orm' === $config['manager_type']) {
            $modelType = 'entity';
        } elseif ('mongodb' === $config['manager_type']) {
            $modelType = 'document';
        }

        $container->setParameter(sprintf('rz_contact.admin.contact.%s', $modelType), $config['class']['contact']['model']);
        $container->setParameter('rz_contact.model.contact.class', $config['class']['contact']['model']);
        $container->setParameter('rz_contact.manager.contact.class', $config['class']['contact']['manager']);

    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureAdminClass($config, ContainerBuilder $container)
    {
        $container->setParameter('rz_contact.admin.contact.class', $config['admin']['contact']['class']);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureTranslationDomain($config, ContainerBuilder $container)
    {
        $container->setParameter('rz_contact.admin.contact.translation_domain', $config['admin']['contact']['translation']);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureController($config, ContainerBuilder $container)
    {
        $container->setParameter('rz_contact.admin.contact.controller', $config['admin']['contact']['controller']);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureRzTemplates($config, ContainerBuilder $container)
    {
        $container->setParameter('rz_contact.configuration.contact.templates', $config['admin']['contact']['templates']);
    }

    /**
     * @param ContainerBuilder $container
     */
    public function configureContactForm($config, ContainerBuilder $container)
    {
        $container->setParameter('rz_contact.form.contact.class', $config['contact']['form']['class']);
        $container->setParameter('rz_contact.form.contact.name', $config['contact']['form']['name']);
        $container->setParameter('rz_contact.form.contact.type', $config['contact']['form']['type']);
        $container->setParameter('rz_contact.form.contact.validation_groups', $config['contact']['form']['validation_groups']);
        $container->setParameter('rz_contact.form.contact.view', $config['contact']['form']['view']);

        $container->setAlias('rz_contact.form.contact.handler', $config['contact']['form']['handler']);
    }

    /**
     * @param ContainerBuilder $container
     */
    public function configureSpamDetection($config, ContainerBuilder $container)
    {
        $container->setParameter('rz_contact.spam_detector.class', $config['spam_detector']['class']);


        if(isset($config['spam_detector']) && isset($config['spam_detector']['service'])) {
            $container->setAlias('rz_spam_detector', $config['spam_detector']['service']);
        } else {
            $container->setAlias('rz_spam_detector', 'rz_contact.spam_detector.stub');
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    public function configureConnectors($config, ContainerBuilder $container)
    {
        // Connectors
        if(!isset($config['connectors']))
        {
            $config['connectors'] = array();
        }

        foreach($config['connectors'] as $connector => $attributes) {
            // custom connectors
            if(array_key_exists("class", (array) $attributes))
            {
                // TODO
                continue;
            }

            // built-in connector configuration
            $mappingMethod = "map" . ucfirst($connector) . "ConnectorParameters";
            if(method_exists($this, $mappingMethod))
            {
                $this->$mappingMethod($attributes, $container);
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    public function configureBlocks($config, ContainerBuilder $container)
    {
        $container->setParameter('rz_contact.block.contact.contact_us_form.class', $config['contact']['block']['class']);
        $container->setParameter('rz_contact.block.contact.contact_us_form.default_template', $config['contact']['block']['default_template']);

        if (isset($config['contact']['block']['templates'])) {

            $templates = $config['contact']['block']['templates'];
            $choices = array();
            foreach($templates as $template) {
                $choices[$template['template']] = $template['label'];
            }
            $container->setParameter('rz_contact.block.contact.contact_us_form.template_choices', $choices);
        } else {
            $container->setParameter('rz_contact.block.contact.contact_us_form.template_choices', array('RzContactBundle:Block:block_contact_us.html.twig'=>'Contact Us Default'));
        }

    }

    public function mapEmailConnectorParameters($config, ContainerBuilder $container)
    {
        $container->setParameter('rz_contact.email.recipients', $config['recipients']);
    }

    public function mapDatabaseConnectorParameters($config, ContainerBuilder $container)
    {
        if(!isset($config['db_driver']) || !in_array(strtolower($config['db_driver']), array('orm', 'mongodb'))) {
            throw new \InvalidArgumentException(sprintf('Invalid db driver "%s".', $config['db_driver']));
        }

        $loader = new XmlFileLoader($container, new FileLocator(array(__DIR__.'/../Resources/config')));
        $loader->load(sprintf('%s.xml', $config['db_driver']));
    }

    protected function remapParameters(array $config, ContainerBuilder $container, array $map)
    {
        foreach ($map as $name => $paramName) {
            if (array_key_exists($name, $config)) {
                $container->setParameter($paramName, $config[$name]);
            }
        }
    }

    protected function remapParametersNamespaces(array $config, ContainerBuilder $container, array $namespaces)
    {
        foreach ($namespaces as $ns => $map) {
            if ($ns) {
                if (!array_key_exists($ns, $config)) {
                    continue;
                }
                $namespaceConfig = $config[$ns];
            } else {
                $namespaceConfig = $config;
            }
            if (is_array($map)) {
                $this->remapParameters($namespaceConfig, $container, $map);
            } else {
                foreach ($namespaceConfig as $name => $value) {
                    $container->setParameter(sprintf($map, $name), $value);
                }
            }
        }
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function configureSettings($config, ContainerBuilder $container)
    {
        $container->setParameter('rz_contact.settings.min_time_to_submit', $config['settings']['min_time_to_submit']);
        $container->setParameter('rz_contact.settings.max_time_to_submit', $config['settings']['max_time_to_submit']);
        $container->setParameter('rz_contact.settings.no_days_to_validate', $config['settings']['no_days_to_validate']);


    }

}