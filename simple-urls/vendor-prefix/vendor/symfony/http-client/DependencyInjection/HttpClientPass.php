<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace LassoLiteVendor\Symfony\Component\HttpClient\DependencyInjection;

use LassoLiteVendor\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use LassoLiteVendor\Symfony\Component\DependencyInjection\ContainerBuilder;
use LassoLiteVendor\Symfony\Component\DependencyInjection\ContainerInterface;
use LassoLiteVendor\Symfony\Component\DependencyInjection\Reference;
use LassoLiteVendor\Symfony\Component\HttpClient\TraceableHttpClient;
final class HttpClientPass implements CompilerPassInterface
{
    private $clientTag;
    public function __construct(string $clientTag = 'http_client.client')
    {
        if (0 < \func_num_args()) {
            trigger_deprecation('symfony/http-client', '5.3', 'Configuring "%s" is deprecated.', __CLASS__);
        }
        $this->clientTag = $clientTag;
    }
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('data_collector.http_client')) {
            return;
        }
        foreach ($container->findTaggedServiceIds($this->clientTag) as $id => $tags) {
            $container->register('.debug.' . $id, TraceableHttpClient::class)->setArguments([new Reference('.debug.' . $id . '.inner'), new Reference('debug.stopwatch', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)])->addTag('kernel.reset', ['method' => 'reset'])->setDecoratedService($id);
            $container->getDefinition('data_collector.http_client')->addMethodCall('registerClient', [$id, new Reference('.debug.' . $id)]);
        }
    }
}
