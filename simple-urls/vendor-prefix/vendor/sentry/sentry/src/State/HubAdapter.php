<?php

declare (strict_types=1);
namespace LassoLiteVendor\Sentry\State;

use LassoLiteVendor\Sentry\Breadcrumb;
use LassoLiteVendor\Sentry\ClientInterface;
use LassoLiteVendor\Sentry\Event;
use LassoLiteVendor\Sentry\EventHint;
use LassoLiteVendor\Sentry\EventId;
use LassoLiteVendor\Sentry\Integration\IntegrationInterface;
use LassoLiteVendor\Sentry\SentrySdk;
use LassoLiteVendor\Sentry\Severity;
use LassoLiteVendor\Sentry\Tracing\Span;
use LassoLiteVendor\Sentry\Tracing\Transaction;
use LassoLiteVendor\Sentry\Tracing\TransactionContext;
/**
 * An implementation of {@see HubInterface} that uses {@see SentrySdk} internally
 * to manage the current hub.
 */
final class HubAdapter implements HubInterface
{
    /**
     * @var self|null The single instance which forwards all calls to {@see SentrySdk}
     */
    private static $instance;
    /**
     * Constructor.
     */
    private function __construct()
    {
    }
    /**
     * Gets the instance of this class. This is a singleton, so once initialized
     * you will always get the same instance.
     */
    public static function getInstance() : self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * {@inheritdoc}
     */
    public function getClient() : ?ClientInterface
    {
        return SentrySdk::getCurrentHub()->getClient();
    }
    /**
     * {@inheritdoc}
     */
    public function getLastEventId() : ?EventId
    {
        return SentrySdk::getCurrentHub()->getLastEventId();
    }
    /**
     * {@inheritdoc}
     */
    public function pushScope() : Scope
    {
        return SentrySdk::getCurrentHub()->pushScope();
    }
    /**
     * {@inheritdoc}
     */
    public function popScope() : bool
    {
        return SentrySdk::getCurrentHub()->popScope();
    }
    /**
     * {@inheritdoc}
     */
    public function withScope(callable $callback)
    {
        return SentrySdk::getCurrentHub()->withScope($callback);
    }
    /**
     * {@inheritdoc}
     */
    public function configureScope(callable $callback) : void
    {
        SentrySdk::getCurrentHub()->configureScope($callback);
    }
    /**
     * {@inheritdoc}
     */
    public function bindClient(ClientInterface $client) : void
    {
        SentrySdk::getCurrentHub()->bindClient($client);
    }
    /**
     * {@inheritdoc}
     */
    public function captureMessage(string $message, ?Severity $level = null, ?EventHint $hint = null) : ?EventId
    {
        return SentrySdk::getCurrentHub()->captureMessage($message, $level, $hint);
    }
    /**
     * {@inheritdoc}
     */
    public function captureException(\Throwable $exception, ?EventHint $hint = null) : ?EventId
    {
        return SentrySdk::getCurrentHub()->captureException($exception, $hint);
    }
    /**
     * {@inheritdoc}
     */
    public function captureEvent(Event $event, ?EventHint $hint = null) : ?EventId
    {
        return SentrySdk::getCurrentHub()->captureEvent($event, $hint);
    }
    /**
     * {@inheritdoc}
     */
    public function captureLastError(?EventHint $hint = null) : ?EventId
    {
        return SentrySdk::getCurrentHub()->captureLastError($hint);
    }
    /**
     * {@inheritdoc}
     */
    public function addBreadcrumb(Breadcrumb $breadcrumb) : bool
    {
        return SentrySdk::getCurrentHub()->addBreadcrumb($breadcrumb);
    }
    /**
     * {@inheritdoc}
     */
    public function getIntegration(string $className) : ?IntegrationInterface
    {
        return SentrySdk::getCurrentHub()->getIntegration($className);
    }
    /**
     * {@inheritdoc}
     *
     * @param array<string, mixed> $customSamplingContext Additional context that will be passed to the {@see SamplingContext}
     */
    public function startTransaction(TransactionContext $context, array $customSamplingContext = []) : Transaction
    {
        return SentrySdk::getCurrentHub()->startTransaction($context, $customSamplingContext);
    }
    /**
     * {@inheritdoc}
     */
    public function getTransaction() : ?Transaction
    {
        return SentrySdk::getCurrentHub()->getTransaction();
    }
    /**
     * {@inheritdoc}
     */
    public function getSpan() : ?Span
    {
        return SentrySdk::getCurrentHub()->getSpan();
    }
    /**
     * {@inheritdoc}
     */
    public function setSpan(?Span $span) : HubInterface
    {
        return SentrySdk::getCurrentHub()->setSpan($span);
    }
    /**
     * @see https://www.php.net/manual/en/language.oop5.cloning.php#object.clone
     */
    public function __clone()
    {
        throw new \BadMethodCallException('Cloning is forbidden.');
    }
    /**
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.wakeup
     */
    public function __wakeup()
    {
        throw new \BadMethodCallException('Unserializing instances of this class is forbidden.');
    }
}
