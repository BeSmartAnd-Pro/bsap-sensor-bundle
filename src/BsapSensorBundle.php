<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapSensorBundle;

use BeSmartAndPro\BsapSensorClient\SensorClient;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class BsapSensorBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('api_token')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('base_uri')->defaultValue(SensorClient::DEFAULT_BASE_URI)->cannotBeEmpty()->end()
                ->floatNode('timeout')->defaultValue(10.0)->min(0.001)->end()
                ->scalarNode('http_client')->defaultValue('http_client')->cannotBeEmpty()->end()
            ->end();
    }

    /** @param array{api_token: string, base_uri: string, timeout: float, http_client: string} $config */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');
        $services = $container->services();

        $services->get('bsap_sensor.http_client')
            ->parent($config['http_client'])
            ->call('withOptions', [[
                'timeout' => $config['timeout'],
                'max_duration' => $config['timeout'],
                'max_redirects' => 0,
            ]], true);

        $services->get(SensorClient::class)
            ->args([
                service('bsap_sensor.psr18_client'),
                service('bsap_sensor.psr17_factory'),
                $config['api_token'],
                $config['base_uri'],
            ]);
    }
}
