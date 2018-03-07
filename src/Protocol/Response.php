<?php
namespace Volantus\Pigpio\Protocol;

/**
 * Class Response
 *
 * @package Volantus\Pigpio\Protocol
 */
class Response
{
    /**
     * Signed 32 bit integer
     *
     * @var int
     */
    private $response;

    /**
     * Dynamic extension data
     *
     * @var array|null
     */
    private $extension;

    /**
     * DefaultResponse constructor.
     *
     * @param int        $response Socket response (p3) - Signed 32 bit integer
     * @param array|null $extension
     */
    public function __construct(int $response, array $extension = null)
    {
        $this->response = $response;
        $this->extension = $extension;
    }

    /**
     * @return int
     */
    public function getResponse(): int
    {
        return $this->response;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->response >= 0;
    }

    /**
     * @return array|null
     */
    public function getExtension()
    {
        return $this->extension;
    }
}