<?php
namespace Volantus\Pigpio\Protocol;

/**
 * Class DefaultResponseStructure
 *
 * @package Volantus\Pigpio\Protocol
 */
class DefaultResponseStructure implements ResponseStructure
{
    /**
     * @param string $data
     *
     * @return Response
     */
    public function decode(string $data): Response
    {
        $data = unpack('Lcmd/Lp1/Lp2/lres', $data);
        return new Response($data['res']);
    }
}