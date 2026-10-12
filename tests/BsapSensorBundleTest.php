<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapSensorBundle\Tests;

use BeSmartAndPro\BsapSensorBundle\BsapSensorBundle;
use BeSmartAndPro\BsapSensorClient\SensorClient;
use BeSmartAndPro\BsapSensorClient\SensorClientInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class BsapSensorBundleTest extends TestCase
{
    public function testCompiledContainerAndHttpOptions(): void
    {
        $builder = $this->container(['api_token' => '%env(SENSOR_API_TOKEN)%', 'timeout' => 3.5]);
        $builder->setParameter('env(SENSOR_API_TOKEN)', 'test.token');
        $builder->setDefinition('http_client', new Definition(MockHttpClient::class)
            ->setArguments([null]));
        $builder->getDefinition(SensorClient::class)->setPublic(true);
        $builder->getAlias(SensorClientInterface::class)->setPublic(true);
        $builder->getDefinition('bsap_sensor.http_client')->setPublic(true);
        $builder->compile(true);

        self::assertSame($builder->get(SensorClient::class), $builder->get(SensorClientInterface::class));
        $httpClient = $builder->get('bsap_sensor.http_client');
        self::assertInstanceOf(MockHttpClient::class, $httpClient);
        $httpClient->setResponseFactory(static function (string $method, string $url, array $options): MockResponse {
            self::assertSame('GET', $method);
            self::assertSame('https://sensor.besmartand.pro/api/client/sensors/id/history', $url);
            self::assertSame(3.5, $options['timeout']);
            self::assertSame(3.5, $options['max_duration']);
            self::assertSame(0, $options['max_redirects']);
            self::assertContains('X-Api-Token: test.token', $options['headers']);

            return new MockResponse('{"items":[]}');
        });

        $client = $builder->get(SensorClientInterface::class);
        self::assertInstanceOf(SensorClientInterface::class, $client);
        self::assertSame([], $client->getSensorHistory('id'));
    }

    public function testCustomHttpClientAndBaseUri(): void
    {
        $builder = $this->container([
            'api_token' => 'test.token',
            'http_client' => 'custom.http_client',
            'base_uri' => 'https://example.com/prefix/',
        ]);
        $builder->register('custom.http_client', MockHttpClient::class)->setArguments([null]);
        $builder->getDefinition(SensorClient::class)->setPublic(true);
        $builder->getDefinition('bsap_sensor.http_client')->setPublic(true);
        $builder->compile(true);
        $httpClient = $builder->get('bsap_sensor.http_client');
        self::assertInstanceOf(MockHttpClient::class, $httpClient);
        $httpClient->setResponseFactory(static function (string $method, string $url): MockResponse {
            self::assertSame('https://example.com/prefix/api/client/sensors/id/history', $url);

            return new MockResponse('{"items":[]}');
        });
        $client = $builder->get(SensorClient::class);
        self::assertInstanceOf(SensorClient::class, $client);
        self::assertSame([], $client->getSensorHistory('id'));
    }

    public function testFrameworkKernelIntegration(): void
    {
        $kernel = new TestKernel('test', true);

        try {
            $kernel->boot();
            self::assertInstanceOf(SensorClient::class, $kernel->getContainer()->get('test.sensor_client'));
        } finally {
            $kernel->shutdown();
        }
    }

    public function testMissingToken(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->container([]);
    }

    public function testInvalidTimeout(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->container(['api_token' => 'test.token', 'timeout' => 0]);
    }

    /** @param array<string, string|float|int> $config */
    private function container(array $config): ContainerBuilder
    {
        $builder = new ContainerBuilder();
        $builder->setParameter('kernel.environment', 'test');
        $builder->setParameter('kernel.build_dir', sys_get_temp_dir() . '/bsap_sensor_extension_test');
        $extension = new BsapSensorBundle()->getContainerExtension();
        self::assertNotNull($extension);
        $extension->load([$config], $builder);

        return $builder;
    }
}
