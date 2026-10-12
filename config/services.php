<?php

declare(strict_types=1);

use BeSmartAndPro\BsapSensorClient\SensorClient;
use BeSmartAndPro\BsapSensorClient\SensorClientInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpClient\Psr18Client;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->set('bsap_sensor.psr17_factory', Psr17Factory::class);
    $services->set('bsap_sensor.http_client');
    $services->set('bsap_sensor.psr18_client', Psr18Client::class)
        ->args([
            service('bsap_sensor.http_client'),
            service('bsap_sensor.psr17_factory'),
            service('bsap_sensor.psr17_factory'),
        ]);

    $services->set(SensorClient::class);
    $services->alias(SensorClientInterface::class, SensorClient::class);
};
