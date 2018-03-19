<?php
namespace Volantus\Pigpio\Protocol;

/**
 * Class ExtensionRequest
 *
 * @package Volantus\Pigpio\Protocol
 */
class ExtensionRequest implements Request
{
    /**
     * Unsigned integer (32 bit)
     * Currently 0 - 177 supported
     *
     * @var int
     */
    private $command;

    /**
     * Unsigned integer (32 bit)
     *
     * @var int
     */
    private $p1;

    /**
     * Unsigned integer (32 bit)
     *
     * @var int
     */
    private $p2;

    /**
     * @var ResponseStructure
     */
    private $responseStructure;

    /**
     * Format used for packing extension data
     *
     * @var string
     */
    private $extensionFormat;

    /**
     * Extension data, appended to socket message
     *
     * @var array
     */
    private $extension;

    /**
     * ExtensionRequest constructor.
     *
     * @param int                    $command            Command (0 - 177)
     * @param int                    $p1                 First parameter - Unsigned integer (32 bit)
     * @param int                    $p2                 Second parameter - Unsigned integer (32 bit)
     * @param string                 $extensionFormat    Packing format of the extension
     * @param array                  $extension          Extension data (needs to match with packing format)
     * @param ResponseStructure      $responseStructure  Structure of the expected response
     */
    public function __construct(int $command, int $p1, int $p2, string $extensionFormat, array $extension, ResponseStructure $responseStructure = null)
    {
        $this->command = $command;
        $this->p1 = $p1;
        $this->p2 = $p2;
        $this->extensionFormat = $extensionFormat;
        $this->extension = $extension;
        $this->responseStructure = $responseStructure ?: new DefaultResponseStructure();
    }

    /**
     * @return string
     */
    public function encode(): string
    {
        $extension = $this->packExtension();
        $length = strlen($extension);

        return pack('LLLL', $this->command, $this->p1, $this->p2, $length) . $extension;
    }

    /**
     * @return ResponseStructure
     */
    public function getResponseStructure(): ResponseStructure
    {
        return $this->responseStructure;
    }

    /**
     * @return string
     */
    private function packExtension(): string
    {
        $packParameters = $this->extension;
        array_unshift($packParameters, $this->extensionFormat);

        return call_user_func_array('pack', $packParameters);
    }

    /**
     * @return int
     */
    public function getCommand(): int
    {
        return $this->command;
    }
}