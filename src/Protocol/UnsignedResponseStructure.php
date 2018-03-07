<?php
namespace Volantus\Pigpio\Protocol;

/**
 * Class UnsignedResponseStructure
 *
 * @package Volantus\Pigpio\Protocol
 */
class UnsignedResponseStructure implements ResponseStructure
{
    /**
     * @param string $data
     *
     * @return Response
     */
    public function decode(string $data): Response
    {
        $data = unpack('Lcmd/Lp1/Lp2/Lres', $data);
        return new Response($data['res']);
    }
}