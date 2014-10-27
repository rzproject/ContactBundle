<?php

/*
 * This file is part of the RzUserBundle package.
 *
 * (c) mell m. zamora <mell@rzproject.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rz\ContactBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('rz_contact');
        $this->addBundleSettings($node);
        return $treeBuilder;
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    private function addBundleSettings(ArrayNodeDefinition $node)
    {
        /**
         * TODO: refactor as not to copy the whole configuration of SonataUserBundle
         * This section will allow RzBundle to override SonataUserBundle via rz_user configuration
         */
        $supportedManagerTypes = array('orm', 'mongodb');

        $node
            ->children()
                ->scalarNode('manager_type')
                    ->defaultValue('orm')
                    ->validate()
                        ->ifNotInArray($supportedManagerTypes)
                        ->thenInvalid('The manager type %s is not supported. Please choose one of '.json_encode($supportedManagerTypes))
                    ->end()
                ->end()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('contact')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('model')->cannotBeEmpty()->defaultValue('Application\\Rz\\ContactBundle\\Entity\\Contact')->end()
                                ->scalarNode('manager')->cannotBeEmpty()->defaultValue('Rz\\ContactBundle\\Manager\\ContactManager')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('contact')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Rz\\ContactBundle\\Admin\\ContactAdmin')->end()
                                ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataAdminBundle:CRUD')->end()
                                ->scalarNode('translation')->cannotBeEmpty()->defaultValue('RzContactBundle')->end()
                                ->arrayNode('templates')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('list')->defaultValue('SonataAdminBundle:CRUD:list.html.twig')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('contact')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->defaultValue('Rz\\ContactBundle\\Form\\Type\\ContactFormType')->end()
                                ->scalarNode('type')->defaultValue('rz_contact_contact')->end()
                                ->scalarNode('handler')->defaultValue('rz_contact.contact.form.handler.default')->end()
                                ->scalarNode('name')->defaultValue('rz_contact_contact_form')->cannotBeEmpty()->end()
                                ->scalarNode('view')->defaultValue('rz_contact_contact_form')->cannotBeEmpty()->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(array('Contact'))
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('connectors')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->arrayNode('email')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('recipients')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(array('contact@rz.net'))
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('database')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('db_driver')->defaultValue('orm')->end()
                            ->end()
                        ->end()
                        ->arrayNode('file')->end()
                    ->end()
                ->end()
                ->arrayNode('spam_detector')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('class')->defaultValue('Rz\\ContactBundle\\SpamDetection\\FalseDetector')->cannotBeEmpty()->end()
                        ->scalarNode('service')->end()
                    ->end()
                ->end()
            ->end();
    }

}
