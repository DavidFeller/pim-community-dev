<?php

namespace Pim\Bundle\CatalogBundle;

use Akeneo\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\ResolveDoctrineTargetRepositoryPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterAttributeConstraintGuessersPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterAttributeTypePass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterComparatorsPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterCompleteCheckerPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterFilterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductQueryFilterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductQuerySorterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductUpdaterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterQueryGeneratorsPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Pim Catalog Bundle
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new ResolveDoctrineTargetModelPass())
            ->addCompilerPass(new ResolveDoctrineTargetRepositoryPass('pim_repository'))
            ->addCompilerPass(new RegisterAttributeConstraintGuessersPass())
            ->addCompilerPass(new RegisterAttributeTypePass())
            ->addCompilerPass(new RegisterQueryGeneratorsPass())
            ->addCompilerPass(new RegisterProductQueryFilterPass())
            ->addCompilerPass(new RegisterProductQuerySorterPass())
            ->addCompilerPass(new RegisterProductUpdaterPass())
            ->addCompilerPass(new RegisterFilterPass())
            ->addCompilerPass(new RegisterComparatorsPass())
            ->addCompilerPass(new RegisterCompleteCheckerPass());

        $productMappings = [
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Pim\Component\Catalog\Model'
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $productMappings,
                ['doctrine.orm.entity_manager'],
                'akeneo_storage_utils.storage_driver.doctrine/orm'
            )
        );

        $currentStorageDriver = $container->get('pim_catalog_product_storage_driver');

        if (AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM === $currentStorageDriver) {
            $mongoDBClass = AkeneoStorageUtilsBundle::DOCTRINE_MONGODB;
            $container->addCompilerPass(
                $mongoDBClass::createYamlMappingDriver(
                    $productMappings,
                    ['doctrine.odm.mongodb.document_manager'],
                    'akeneo_storage_utils.storage_driver.doctrine/mongodb-odm'
                )
            );
        }
    }
}
