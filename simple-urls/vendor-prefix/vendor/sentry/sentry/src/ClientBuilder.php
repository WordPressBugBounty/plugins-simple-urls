<?php

declare (strict_types=1);
namespace LassoLiteVendor\Sentry;

use LassoLiteVendor\Http\Discovery\Psr17FactoryDiscovery;
use LassoLiteVendor\Jean85\PrettyVersions;
use LassoLiteVendor\Psr\Log\LoggerInterface;
use LassoLiteVendor\Sentry\HttpClient\HttpClientFactory;
use LassoLiteVendor\Sentry\Serializer\RepresentationSerializerInterface;
use LassoLiteVendor\Sentry\Serializer\SerializerInterface;
use LassoLiteVendor\Sentry\Transport\DefaultTransportFactory;
use LassoLiteVendor\Sentry\Transport\TransportFactoryInterface;
use LassoLiteVendor\Sentry\Transport\TransportInterface;
/**
 * The default implementation of {@link ClientBuilderInterface}.
 *
 * @author Stefano Arlandini <sarlandini@alice.it>
 */
final class ClientBuilder implements ClientBuilderInterface
{
    /**
     * @var Options The client options
     */
    private $options;
    /**
     * @var TransportFactoryInterface|null The transport factory
     */
    private $transportFactory;
    /**
     * @var TransportInterface|null The transport
     */
    private $transport;
    /**
     * @var SerializerInterface|null The serializer to be injected in the client
     */
    private $serializer;
    /**
     * @var RepresentationSerializerInterface|null The representation serializer to be injected in the client
     */
    private $representationSerializer;
    /**
     * @var LoggerInterface|null A PSR-3 logger to log internal errors and debug messages
     */
    private $logger;
    /**
     * @var string The SDK identifier, to be used in {@see Event} and {@see SentryAuth}
     */
    private $sdkIdentifier = Client::SDK_IDENTIFIER;
    /**
     * @var string The SDK version of the Client
     */
    private $sdkVersion;
    /**
     * Class constructor.
     *
     * @param Options|null $options The client options
     */
    public function __construct(Options $options = null)
    {
        $this->options = $options ?? new Options();
        $this->sdkVersion = PrettyVersions::getVersion('sentry/sentry')->getPrettyVersion();
    }
    /**
     * {@inheritdoc}
     */
    public static function create(array $options = []) : ClientBuilderInterface
    {
        return new self(new Options($options));
    }
    /**
     * {@inheritdoc}
     */
    public function getOptions() : Options
    {
        return $this->options;
    }
    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer) : ClientBuilderInterface
    {
        $this->serializer = $serializer;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function setRepresentationSerializer(RepresentationSerializerInterface $representationSerializer) : ClientBuilderInterface
    {
        $this->representationSerializer = $representationSerializer;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger) : ClientBuilderInterface
    {
        $this->logger = $logger;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function setSdkIdentifier(string $sdkIdentifier) : ClientBuilderInterface
    {
        $this->sdkIdentifier = $sdkIdentifier;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function setSdkVersion(string $sdkVersion) : ClientBuilderInterface
    {
        $this->sdkVersion = $sdkVersion;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function setTransportFactory(TransportFactoryInterface $transportFactory) : ClientBuilderInterface
    {
        $this->transportFactory = $transportFactory;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function getClient() : ClientInterface
    {
        $this->transport = $this->transport ?? $this->createTransportInstance();
        return new Client($this->options, $this->transport, $this->sdkIdentifier, $this->sdkVersion, $this->serializer, $this->representationSerializer, $this->logger);
    }
    /**
     * Creates a new instance of the transport mechanism.
     */
    private function createTransportInstance() : TransportInterface
    {
        if (null !== $this->transport) {
            return $this->transport;
        }
        $transportFactory = $this->transportFactory ?? $this->createDefaultTransportFactory();
        return $transportFactory->create($this->options);
    }
    /**
     * Creates a new instance of the {@see DefaultTransportFactory} factory.
     */
    private function createDefaultTransportFactory() : DefaultTransportFactory
    {
        $streamFactory = Psr17FactoryDiscovery::findStreamFactory();
        $httpClientFactory = new HttpClientFactory(Psr17FactoryDiscovery::findUriFactory(), Psr17FactoryDiscovery::findResponseFactory(), $streamFactory, null, $this->sdkIdentifier, $this->sdkVersion);
        return new DefaultTransportFactory($streamFactory, Psr17FactoryDiscovery::findRequestFactory(), $httpClientFactory, $this->logger);
    }
}
