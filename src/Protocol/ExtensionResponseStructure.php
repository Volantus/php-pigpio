<?php
namespace Volantus\Pigpio\Protocol;

/**
 * Class ExtensionResponseStructure
 *
 * @package Volantus\Pigpio\Protocol
 */
class ExtensionResponseStructure implements ResponseStructure
{
    /**
     * @var string
     */
    private $extensionFormat;

    /**
     * ExtensionResponseStructure constructor.
     *
     * @param string $extensionFormat
     */
    public function __construct($extensionFormat)
    {
        $this->extensionFormat = $extensionFormat;
    }


    /**
     * @param string $data
     *
     * @return Response
     */
    public function decode(string $data): Response
    {
        $baseData = unpack('Lcmd/Lp1/Lp2/lres', substr($data, 0, 16));
        $extensionData = unpack($this->extensionFormat, substr($data, 16, $baseData['res']));
        return new Response($baseData['res'], $extensionData);
    }
}