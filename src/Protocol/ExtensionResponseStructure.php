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
     * @throws IncompleteDataException
     */
    public function decode(string $data): Response
    {
        $baseData = unpack('Lcmd/Lp1/Lp2/lres', substr($data, 0, Response::BASE_SIZE));
        $extensionSize = $baseData['res'];
        $fullSize = $extensionSize + Response::BASE_SIZE;

        if (strlen($data) < $fullSize) {
            throw new IncompleteDataException("Received data is incomplete => expected $fullSize bytes, but got " . strlen($data), $fullSize);
        }

        $extensionData = unpack($this->extensionFormat, substr($data, Response::BASE_SIZE, $extensionSize));
        return new Response($baseData['res'], $extensionData);
    }
}