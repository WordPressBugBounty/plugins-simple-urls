<?php

namespace LassoLiteVendor\Composer;

use LassoLiteVendor\Composer\Autoload\ClassLoader;
use LassoLiteVendor\Composer\Semver\VersionParser;
class InstalledVersions
{
    private static $installed = array('root' => array('pretty_version' => 'dev-master', 'version' => 'dev-master', 'aliases' => array(), 'reference' => '20b0eaef08d35f96f3e5c3ee95ba212441558120', 'name' => 'studiopress/simple-urls'), 'versions' => array('bamarni/composer-bin-plugin' => array('pretty_version' => '1.8.0', 'version' => '1.8.0.0', 'aliases' => array(), 'reference' => '24764700027bcd3cb072e5f8005d4a150fe714fe'), 'clue/stream-filter' => array('pretty_version' => 'v1.6.0', 'version' => '1.6.0.0', 'aliases' => array(), 'reference' => 'd6169430c7731d8509da7aecd0af756a5747b78e'), 'firebase/php-jwt' => array('pretty_version' => 'v6.3.0', 'version' => '6.3.0.0', 'aliases' => array(), 'reference' => '018dfc4e1da92ad8a1b90adc4893f476a3b41cb8'), 'guzzlehttp/promises' => array('pretty_version' => '1.5.1', 'version' => '1.5.1.0', 'aliases' => array(), 'reference' => 'fe752aedc9fd8fcca3fe7ad05d419d32998a06da'), 'guzzlehttp/psr7' => array('pretty_version' => '2.4.0', 'version' => '2.4.0.0', 'aliases' => array(), 'reference' => '13388f00956b1503577598873fffb5ae994b5737'), 'http-interop/http-factory-guzzle' => array('pretty_version' => '1.1.1', 'version' => '1.1.1.0', 'aliases' => array(), 'reference' => '6e1efa1e020bf1c47cf0f13654e8ef9efb1463b3'), 'jean85/pretty-package-versions' => array('pretty_version' => '2.0.5', 'version' => '2.0.5.0', 'aliases' => array(), 'reference' => 'ae547e455a3d8babd07b96966b17d7fd21d9c6af'), 'php-http/async-client-implementation' => array('provided' => array(0 => '*')), 'php-http/client-common' => array('pretty_version' => '2.5.0', 'version' => '2.5.0.0', 'aliases' => array(), 'reference' => 'd135751167d57e27c74de674d6a30cef2dc8e054'), 'php-http/client-implementation' => array('provided' => array(0 => '*')), 'php-http/discovery' => array('pretty_version' => '1.14.3', 'version' => '1.14.3.0', 'aliases' => array(), 'reference' => '31d8ee46d0215108df16a8527c7438e96a4d7735'), 'php-http/httplug' => array('pretty_version' => '2.3.0', 'version' => '2.3.0.0', 'aliases' => array(), 'reference' => 'f640739f80dfa1152533976e3c112477f69274eb'), 'php-http/message' => array('pretty_version' => '1.13.0', 'version' => '1.13.0.0', 'aliases' => array(), 'reference' => '7886e647a30a966a1a8d1dad1845b71ca8678361'), 'php-http/message-factory' => array('pretty_version' => 'v1.0.2', 'version' => '1.0.2.0', 'aliases' => array(), 'reference' => 'a478cb11f66a6ac48d8954216cfed9aa06a501a1'), 'php-http/message-factory-implementation' => array('provided' => array(0 => '1.0')), 'php-http/promise' => array('pretty_version' => '1.1.0', 'version' => '1.1.0.0', 'aliases' => array(), 'reference' => '4c4c1f9b7289a2ec57cde7f1e9762a5789506f88'), 'psr/container' => array('pretty_version' => '1.1.1', 'version' => '1.1.1.0', 'aliases' => array(), 'reference' => '8622567409010282b7aeebe4bb841fe98b58dcaf'), 'psr/http-client' => array('pretty_version' => '1.0.1', 'version' => '1.0.1.0', 'aliases' => array(), 'reference' => '2dfb5f6c5eff0e91e20e913f8c5452ed95b86621'), 'psr/http-client-implementation' => array('provided' => array(0 => '1.0')), 'psr/http-factory' => array('pretty_version' => '1.0.1', 'version' => '1.0.1.0', 'aliases' => array(), 'reference' => '12ac7fcd07e5b077433f5f2bee95b3a771bf61be'), 'psr/http-factory-implementation' => array('provided' => array(0 => '1.0', 1 => '^1.0')), 'psr/http-message' => array('pretty_version' => '1.0.1', 'version' => '1.0.1.0', 'aliases' => array(), 'reference' => 'f6561bf28d520154e4b0ec72be95418abe6d9363'), 'psr/http-message-implementation' => array('provided' => array(0 => '1.0')), 'psr/log' => array('pretty_version' => '1.1.4', 'version' => '1.1.4.0', 'aliases' => array(), 'reference' => 'd49695b909c3b7628b6289db5479a1c204601f11'), 'ralouphie/getallheaders' => array('pretty_version' => '3.0.3', 'version' => '3.0.3.0', 'aliases' => array(), 'reference' => '120b605dfeb996808c31b6477290a714d356e822'), 'sentry/sdk' => array('pretty_version' => '3.2.0', 'version' => '3.2.0.0', 'aliases' => array(), 'reference' => '6d78bd83b43efbb52f81d6824f4af344fa9ba292'), 'sentry/sentry' => array('pretty_version' => '3.7.0', 'version' => '3.7.0.0', 'aliases' => array(), 'reference' => '877bca3f0f0ac0fc8ec0a218c6070cccea266795'), 'studiopress/simple-urls' => array('pretty_version' => 'dev-master', 'version' => 'dev-master', 'aliases' => array(), 'reference' => '20b0eaef08d35f96f3e5c3ee95ba212441558120'), 'symfony/deprecation-contracts' => array('pretty_version' => 'v2.5.2', 'version' => '2.5.2.0', 'aliases' => array(), 'reference' => 'e8b495ea28c1d97b5e0c121748d6f9b53d075c66'), 'symfony/http-client' => array('pretty_version' => 'v5.4.11', 'version' => '5.4.11.0', 'aliases' => array(), 'reference' => '5c5c37eb2a276d8d7d669dd76688aa1606ee78fb'), 'symfony/http-client-contracts' => array('pretty_version' => 'v2.5.2', 'version' => '2.5.2.0', 'aliases' => array(), 'reference' => 'ba6a9f0e8f3edd190520ee3b9a958596b6ca2e70'), 'symfony/http-client-implementation' => array('provided' => array(0 => '2.4')), 'symfony/options-resolver' => array('pretty_version' => 'v5.4.11', 'version' => '5.4.11.0', 'aliases' => array(), 'reference' => '54f14e36aa73cb8f7261d7686691fd4d75ea2690'), 'symfony/polyfill-php73' => array('pretty_version' => 'v1.26.0', 'version' => '1.26.0.0', 'aliases' => array(), 'reference' => 'e440d35fa0286f77fb45b79a03fedbeda9307e85'), 'symfony/polyfill-php80' => array('pretty_version' => 'v1.26.0', 'version' => '1.26.0.0', 'aliases' => array(), 'reference' => 'cfa0ae98841b9e461207c13ab093d76b0fa7bace'), 'symfony/polyfill-uuid' => array('pretty_version' => 'v1.26.0', 'version' => '1.26.0.0', 'aliases' => array(), 'reference' => 'a41886c1c81dc075a09c71fe6db5b9d68c79de23'), 'symfony/service-contracts' => array('pretty_version' => 'v2.5.2', 'version' => '2.5.2.0', 'aliases' => array(), 'reference' => '4b426aac47d6427cc1a1d0f7e2ac724627f5966c')));
    private static $canGetVendors;
    private static $installedByVendor = array();
    public static function getInstalledPackages()
    {
        $packages = array();
        foreach (self::getInstalled() as $installed) {
            $packages[] = \array_keys($installed['versions']);
        }
        if (1 === \count($packages)) {
            return $packages[0];
        }
        return \array_keys(\array_flip(\call_user_func_array('array_merge', $packages)));
    }
    public static function isInstalled($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (isset($installed['versions'][$packageName])) {
                return \true;
            }
        }
        return \false;
    }
    public static function satisfies(VersionParser $parser, $packageName, $constraint)
    {
        $constraint = $parser->parseConstraints($constraint);
        $provided = $parser->parseConstraints(self::getVersionRanges($packageName));
        return $provided->matches($constraint);
    }
    public static function getVersionRanges($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            $ranges = array();
            if (isset($installed['versions'][$packageName]['pretty_version'])) {
                $ranges[] = $installed['versions'][$packageName]['pretty_version'];
            }
            if (\array_key_exists('aliases', $installed['versions'][$packageName])) {
                $ranges = \array_merge($ranges, $installed['versions'][$packageName]['aliases']);
            }
            if (\array_key_exists('replaced', $installed['versions'][$packageName])) {
                $ranges = \array_merge($ranges, $installed['versions'][$packageName]['replaced']);
            }
            if (\array_key_exists('provided', $installed['versions'][$packageName])) {
                $ranges = \array_merge($ranges, $installed['versions'][$packageName]['provided']);
            }
            return \implode(' || ', $ranges);
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getVersion($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            if (!isset($installed['versions'][$packageName]['version'])) {
                return null;
            }
            return $installed['versions'][$packageName]['version'];
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getPrettyVersion($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            if (!isset($installed['versions'][$packageName]['pretty_version'])) {
                return null;
            }
            return $installed['versions'][$packageName]['pretty_version'];
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getReference($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            if (!isset($installed['versions'][$packageName]['reference'])) {
                return null;
            }
            return $installed['versions'][$packageName]['reference'];
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getRootPackage()
    {
        $installed = self::getInstalled();
        return $installed[0]['root'];
    }
    public static function getRawData()
    {
        return self::$installed;
    }
    public static function reload($data)
    {
        self::$installed = $data;
        self::$installedByVendor = array();
    }
    private static function getInstalled()
    {
        if (null === self::$canGetVendors) {
            self::$canGetVendors = \method_exists('LassoLiteVendor\\Composer\\Autoload\\ClassLoader', 'getRegisteredLoaders');
        }
        $installed = array();
        if (self::$canGetVendors) {
            foreach (ClassLoader::getRegisteredLoaders() as $vendorDir => $loader) {
                if (isset(self::$installedByVendor[$vendorDir])) {
                    $installed[] = self::$installedByVendor[$vendorDir];
                } elseif (\is_file($vendorDir . '/composer/installed.php')) {
                    $installed[] = self::$installedByVendor[$vendorDir] = (require $vendorDir . '/composer/installed.php');
                }
            }
        }
        $installed[] = self::$installed;
        return $installed;
    }
}
