<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapSensorBundle\Tests;

use BeSmartAndPro\BsapSensorBundle\BsapSensorBundle;
use BeSmartAndPro\BsapSensorClient\SensorClientInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /** @return iterable<BundleInterface> */
    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new BsapSensorBundle();
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/bsap_sensor_bundle_test/' . Kernel::VERSION;
    }

    public function getProjectDir(): string
    {
        return $this->getCacheDir() . '/project';
    }

    public function getLogDir(): string
    {
        return $this->getCacheDir() . '/logs';
    }

    private function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test-secret',
            'http_client' => [],
        ]);
        $container->extension('bsap_sensor', ['api_token' => 'test.token']);
        $container->services()->alias('test.sensor_client', SensorClientInterface::class)->public();
    }
}
