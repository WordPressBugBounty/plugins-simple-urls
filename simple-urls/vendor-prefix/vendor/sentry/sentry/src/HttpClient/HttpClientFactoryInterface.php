<?php

declare (strict_types=1);
namespace LassoLiteVendor\Sentry\HttpClient;

use LassoLiteVendor\Http\Client\HttpAsyncClient as HttpAsyncClientInterface;
use LassoLiteVendor\Sentry\Options;
/**
 * This interface defines a contract for classes willing to serve as factories
 * for the HTTP client.
 */
interface HttpClientFactoryInterface
{
    /**
     * Create HTTP Client wrapped with configured plugins.
     *
     * @param Options $options The client options
     */
    public function create(Options $options) : HttpAsyncClientInterface;
}
