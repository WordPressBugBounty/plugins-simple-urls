<?php

declare (strict_types=1);
namespace LassoLiteVendor\Sentry\Monolog;

use LassoLiteVendor\Monolog\Handler\AbstractProcessingHandler;
use LassoLiteVendor\Monolog\Logger;
use LassoLiteVendor\Monolog\LogRecord;
use LassoLiteVendor\Sentry\Event;
use LassoLiteVendor\Sentry\EventHint;
use LassoLiteVendor\Sentry\State\HubInterface;
use LassoLiteVendor\Sentry\State\Scope;
/**
 * This Monolog handler logs every message to a Sentry's server using the given
 * hub instance.
 *
 * @author Stefano Arlandini <sarlandini@alice.it>
 */
final class Handler extends AbstractProcessingHandler
{
    use CompatibilityProcessingHandlerTrait;
    private const CONTEXT_EXCEPTION_KEY = 'exception';
    /**
     * @var HubInterface
     */
    private $hub;
    /**
     * @var bool
     */
    private $fillExtraContext;
    /**
     * {@inheritdoc}
     *
     * @param HubInterface $hub The hub to which errors are reported
     */
    public function __construct(HubInterface $hub, $level = Logger::DEBUG, bool $bubble = \true, bool $fillExtraContext = \false)
    {
        parent::__construct($level, $bubble);
        $this->hub = $hub;
        $this->fillExtraContext = $fillExtraContext;
    }
    /**
     * @param array<string, mixed>|LogRecord $record
     */
    protected function doWrite($record) : void
    {
        $event = Event::createEvent();
        $event->setLevel(self::getSeverityFromLevel($record['level']));
        $event->setMessage($record['message']);
        $event->setLogger(\sprintf('monolog.%s', $record['channel']));
        $hint = new EventHint();
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Throwable) {
            $hint->exception = $record['context']['exception'];
        }
        $this->hub->withScope(function (Scope $scope) use($record, $event, $hint) : void {
            $scope->setExtra('monolog.channel', $record['channel']);
            $scope->setExtra('monolog.level', $record['level_name']);
            $monologContextData = $this->getMonologContextData($record['context']);
            if ([] !== $monologContextData) {
                $scope->setExtra('monolog.context', $monologContextData);
            }
            $monologExtraData = $this->getMonologExtraData($record['extra']);
            if ([] !== $monologExtraData) {
                $scope->setExtra('monolog.extra', $monologExtraData);
            }
            $this->hub->captureEvent($event, $hint);
        });
    }
    /**
     * @param mixed[] $context
     *
     * @return mixed[]
     */
    private function getMonologContextData(array $context) : array
    {
        if (!$this->fillExtraContext) {
            return [];
        }
        $contextData = [];
        foreach ($context as $key => $value) {
            // We skip the `exception` field because it goes in its own context
            if (self::CONTEXT_EXCEPTION_KEY === $key) {
                continue;
            }
            $contextData[$key] = $value;
        }
        return $contextData;
    }
    /**
     * @param mixed[] $context
     *
     * @return mixed[]
     */
    private function getMonologExtraData(array $context) : array
    {
        if (!$this->fillExtraContext) {
            return [];
        }
        $extraData = [];
        foreach ($context as $key => $value) {
            $extraData[$key] = $value;
        }
        return $extraData;
    }
}
