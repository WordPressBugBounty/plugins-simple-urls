<?php

declare (strict_types=1);
namespace LassoLiteVendor\Sentry\Tracing;

/**
 * This class represents an span ID.
 */
final class SpanId
{
    /**
     * @var string The ID
     */
    private $value;
    /**
     * Class constructor.
     *
     * @param string $value The ID
     */
    public function __construct(string $value)
    {
        if (!\preg_match('/^[a-f0-9]{16}$/i', $value)) {
            throw new \InvalidArgumentException('The $value argument must be a 16 characters long hexadecimal string.');
        }
        $this->value = $value;
    }
    /**
     * Generates a new span ID.
     */
    public static function generate() : self
    {
        return new self(\substr(\str_replace('-', '', \uuid_create(\UUID_TYPE_RANDOM)), 0, 16));
    }
    /**
     * Compares whether two objects are equals.
     *
     * @param SpanId $other The object to compare
     */
    public function isEqualTo(self $other) : bool
    {
        return $this->value === $other->value;
    }
    public function __toString() : string
    {
        return $this->value;
    }
}
