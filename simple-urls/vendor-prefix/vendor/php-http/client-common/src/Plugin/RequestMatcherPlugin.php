<?php

declare (strict_types=1);
namespace LassoLiteVendor\Http\Client\Common\Plugin;

use LassoLiteVendor\Http\Client\Common\Plugin;
use LassoLiteVendor\Http\Message\RequestMatcher;
use LassoLiteVendor\Http\Promise\Promise;
use LassoLiteVendor\Psr\Http\Message\RequestInterface;
/**
 * Apply a delegated plugin based on a request match.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class RequestMatcherPlugin implements Plugin
{
    /**
     * @var RequestMatcher
     */
    private $requestMatcher;
    /**
     * @var Plugin|null
     */
    private $successPlugin;
    /**
     * @var Plugin|null
     */
    private $failurePlugin;
    public function __construct(RequestMatcher $requestMatcher, ?Plugin $delegateOnMatch, Plugin $delegateOnNoMatch = null)
    {
        $this->requestMatcher = $requestMatcher;
        $this->successPlugin = $delegateOnMatch;
        $this->failurePlugin = $delegateOnNoMatch;
    }
    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first) : Promise
    {
        if ($this->requestMatcher->matches($request)) {
            if (null !== $this->successPlugin) {
                return $this->successPlugin->handleRequest($request, $next, $first);
            }
        } elseif (null !== $this->failurePlugin) {
            return $this->failurePlugin->handleRequest($request, $next, $first);
        }
        return $next($request);
    }
}