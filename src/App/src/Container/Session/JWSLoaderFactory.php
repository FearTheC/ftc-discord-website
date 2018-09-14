<?php

declare(strict_types=1);

namespace App\Container\Session;

use Psr\Container\ContainerInterface;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Core\Converter\StandardConverter;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Algorithm\ES512;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Checker\HeaderCheckerManager;

class JWSLoaderFactory
{
    
    public function __invoke(ContainerInterface $container) : JWSLoader
    {
        $jsonConverter = new StandardConverter();
        $serializerManager = JWSSerializerManager::create([new CompactSerializer($jsonConverter)]);
        
        $algorithmManager = AlgorithmManager::create([new ES512()]);
        $jwsVerifier = new JWSVerifier($algorithmManager);
        
        $headerCheckerManager = HeaderCheckerManager::create(
            [new AlgorithmChecker(['ES512']),],
            [new JWSTokenSupport(),]
        );
        
        return new JWSLoader($serializerManager, $jwsVerifier, $headerCheckerManager);
        
    }
    
}
