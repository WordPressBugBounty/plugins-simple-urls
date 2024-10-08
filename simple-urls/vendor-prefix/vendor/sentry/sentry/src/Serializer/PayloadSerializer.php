<?php

declare (strict_types=1);
namespace LassoLiteVendor\Sentry\Serializer;

use LassoLiteVendor\Sentry\Breadcrumb;
use LassoLiteVendor\Sentry\Event;
use LassoLiteVendor\Sentry\EventType;
use LassoLiteVendor\Sentry\ExceptionDataBag;
use LassoLiteVendor\Sentry\Frame;
use LassoLiteVendor\Sentry\Tracing\Span;
use LassoLiteVendor\Sentry\Util\JSON;
/**
 * This is a simple implementation of a serializer that takes in input an event
 * object and returns a serialized string ready to be sent off to Sentry.
 *
 * @internal
 */
final class PayloadSerializer implements PayloadSerializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function serialize(Event $event) : string
    {
        if (EventType::transaction() === $event->getType()) {
            return $this->serializeAsEnvelope($event);
        }
        return $this->serializeAsEvent($event);
    }
    private function serializeAsEvent(Event $event) : string
    {
        $result = $this->toArray($event);
        return JSON::encode($result);
    }
    /**
     * @return array<string, mixed>
     */
    public function toArray(Event $event) : array
    {
        $result = ['event_id' => (string) $event->getId(), 'timestamp' => $event->getTimestamp(), 'platform' => 'php', 'sdk' => ['name' => $event->getSdkIdentifier(), 'version' => $event->getSdkVersion()]];
        if (null !== $event->getStartTimestamp()) {
            $result['start_timestamp'] = $event->getStartTimestamp();
        }
        if (null !== $event->getLevel()) {
            $result['level'] = (string) $event->getLevel();
        }
        if (null !== $event->getLogger()) {
            $result['logger'] = $event->getLogger();
        }
        if (null !== $event->getTransaction()) {
            $result['transaction'] = $event->getTransaction();
        }
        if (null !== $event->getServerName()) {
            $result['server_name'] = $event->getServerName();
        }
        if (null !== $event->getRelease()) {
            $result['release'] = $event->getRelease();
        }
        if (null !== $event->getEnvironment()) {
            $result['environment'] = $event->getEnvironment();
        }
        if (!empty($event->getFingerprint())) {
            $result['fingerprint'] = $event->getFingerprint();
        }
        if (!empty($event->getModules())) {
            $result['modules'] = $event->getModules();
        }
        if (!empty($event->getExtra())) {
            $result['extra'] = $event->getExtra();
        }
        if (!empty($event->getTags())) {
            $result['tags'] = $event->getTags();
        }
        $user = $event->getUser();
        if (null !== $user) {
            $result['user'] = \array_merge($user->getMetadata(), ['id' => $user->getId(), 'username' => $user->getUsername(), 'email' => $user->getEmail(), 'ip_address' => $user->getIpAddress()]);
        }
        $osContext = $event->getOsContext();
        $runtimeContext = $event->getRuntimeContext();
        if (null !== $osContext) {
            $result['contexts']['os'] = ['name' => $osContext->getName(), 'version' => $osContext->getVersion(), 'build' => $osContext->getBuild(), 'kernel_version' => $osContext->getKernelVersion()];
        }
        if (null !== $runtimeContext) {
            $result['contexts']['runtime'] = ['name' => $runtimeContext->getName(), 'version' => $runtimeContext->getVersion()];
        }
        if (!empty($event->getContexts())) {
            $result['contexts'] = \array_merge($result['contexts'] ?? [], $event->getContexts());
        }
        if (!empty($event->getBreadcrumbs())) {
            $result['breadcrumbs']['values'] = \array_map([$this, 'serializeBreadcrumb'], $event->getBreadcrumbs());
        }
        if (!empty($event->getRequest())) {
            $result['request'] = $event->getRequest();
        }
        if (null !== $event->getMessage()) {
            if (empty($event->getMessageParams())) {
                $result['message'] = $event->getMessage();
            } else {
                $result['message'] = ['message' => $event->getMessage(), 'params' => $event->getMessageParams(), 'formatted' => $event->getMessageFormatted() ?? \vsprintf($event->getMessage(), $event->getMessageParams())];
            }
        }
        $exceptions = $event->getExceptions();
        for ($i = \count($exceptions) - 1; $i >= 0; --$i) {
            $result['exception']['values'][] = $this->serializeException($exceptions[$i]);
        }
        if (EventType::transaction() === $event->getType()) {
            $result['spans'] = \array_values(\array_map([$this, 'serializeSpan'], $event->getSpans()));
        }
        $stacktrace = $event->getStacktrace();
        if (null !== $stacktrace) {
            $result['stacktrace'] = ['frames' => \array_map([$this, 'serializeStacktraceFrame'], $stacktrace->getFrames())];
        }
        return $result;
    }
    private function serializeAsEnvelope(Event $event) : string
    {
        $envelopeHeader = JSON::encode(['event_id' => (string) $event->getId(), 'sent_at' => \gmdate('Y-m-d\\TH:i:s\\Z')]);
        $itemHeader = JSON::encode(['type' => (string) $event->getType(), 'content_type' => 'application/json']);
        return \sprintf("%s\n%s\n%s", $envelopeHeader, $itemHeader, $this->serializeAsEvent($event));
    }
    /**
     * @return array<string, mixed>
     *
     * @psalm-return array{
     *     type: string,
     *     category: string,
     *     level: string,
     *     timestamp: float,
     *     message?: string,
     *     data?: array<string, mixed>
     * }
     */
    private function serializeBreadcrumb(Breadcrumb $breadcrumb) : array
    {
        $result = ['type' => $breadcrumb->getType(), 'category' => $breadcrumb->getCategory(), 'level' => $breadcrumb->getLevel(), 'timestamp' => $breadcrumb->getTimestamp()];
        if (null !== $breadcrumb->getMessage()) {
            $result['message'] = $breadcrumb->getMessage();
        }
        if (!empty($breadcrumb->getMetadata())) {
            $result['data'] = $breadcrumb->getMetadata();
        }
        return $result;
    }
    /**
     * @return array<string, mixed>
     *
     * @psalm-return array{
     *     type: string,
     *     value: string,
     *     stacktrace?: array{
     *         frames: array<array<string, mixed>>
     *     },
     *     mechanism?: array{
     *         type: string,
     *         handled: boolean
     *     }
     * }
     */
    private function serializeException(ExceptionDataBag $exception) : array
    {
        $exceptionMechanism = $exception->getMechanism();
        $exceptionStacktrace = $exception->getStacktrace();
        $result = ['type' => $exception->getType(), 'value' => $exception->getValue()];
        if (null !== $exceptionStacktrace) {
            $result['stacktrace'] = ['frames' => \array_map([$this, 'serializeStacktraceFrame'], $exceptionStacktrace->getFrames())];
        }
        if (null !== $exceptionMechanism) {
            $result['mechanism'] = ['type' => $exceptionMechanism->getType(), 'handled' => $exceptionMechanism->isHandled()];
        }
        return $result;
    }
    /**
     * @return array<string, mixed>
     *
     * @psalm-return array{
     *     filename: string,
     *     lineno: int,
     *     in_app: bool,
     *     abs_path?: string,
     *     function?: string,
     *     raw_function?: string,
     *     pre_context?: string[],
     *     context_line?: string,
     *     post_context?: string[],
     *     vars?: array<string, mixed>
     * }
     */
    private function serializeStacktraceFrame(Frame $frame) : array
    {
        $result = ['filename' => $frame->getFile(), 'lineno' => $frame->getLine(), 'in_app' => $frame->isInApp()];
        if (null !== $frame->getAbsoluteFilePath()) {
            $result['abs_path'] = $frame->getAbsoluteFilePath();
        }
        if (null !== $frame->getFunctionName()) {
            $result['function'] = $frame->getFunctionName();
        }
        if (null !== $frame->getRawFunctionName()) {
            $result['raw_function'] = $frame->getRawFunctionName();
        }
        if (!empty($frame->getPreContext())) {
            $result['pre_context'] = $frame->getPreContext();
        }
        if (null !== $frame->getContextLine()) {
            $result['context_line'] = $frame->getContextLine();
        }
        if (!empty($frame->getPostContext())) {
            $result['post_context'] = $frame->getPostContext();
        }
        if (!empty($frame->getVars())) {
            $result['vars'] = $frame->getVars();
        }
        return $result;
    }
    /**
     * @return array<string, mixed>
     *
     * @psalm-return array{
     *     span_id: string,
     *     trace_id: string,
     *     parent_span_id?: string,
     *     start_timestamp: float,
     *     timestamp?: float,
     *     status?: string,
     *     description?: string,
     *     op?: string,
     *     data?: array<string, mixed>,
     *     tags?: array<string, string>
     * }
     */
    private function serializeSpan(Span $span) : array
    {
        $result = ['span_id' => (string) $span->getSpanId(), 'trace_id' => (string) $span->getTraceId(), 'start_timestamp' => $span->getStartTimestamp()];
        if (null !== $span->getParentSpanId()) {
            $result['parent_span_id'] = (string) $span->getParentSpanId();
        }
        if (null !== $span->getEndTimestamp()) {
            $result['timestamp'] = $span->getEndTimestamp();
        }
        if (null !== $span->getStatus()) {
            $result['status'] = (string) $span->getStatus();
        }
        if (null !== $span->getDescription()) {
            $result['description'] = $span->getDescription();
        }
        if (null !== $span->getOp()) {
            $result['op'] = $span->getOp();
        }
        if (!empty($span->getData())) {
            $result['data'] = $span->getData();
        }
        if (!empty($span->getTags())) {
            $result['tags'] = $span->getTags();
        }
        return $result;
    }
}
