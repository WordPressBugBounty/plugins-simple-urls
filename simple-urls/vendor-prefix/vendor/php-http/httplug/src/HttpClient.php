<?php

namespace LassoLiteVendor\Http\Client;

use LassoLiteVendor\Psr\Http\Client\ClientInterface;
/**
 * {@inheritdoc}
 *
 * Provide the Httplug HttpClient interface for BC.
 * You should typehint Psr\Http\Client\ClientInterface in new code
 */
interface HttpClient extends ClientInterface
{
}
