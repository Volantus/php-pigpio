<?php
namespace Volantus\Pigpio\Notification\Event;

/**
 * Class EventFactory
 *
 * @package Volantus\Pigpio\Notification\Event
 */
class EventFactory
{
    const PI_NTFY_FLAGS_WDOG  = 1 << 5;
    const PI_NTFY_FLAGS_ALIVE = 1 << 6;
    const PI_NTFY_FLAGS_EVENT = 1 << 7;

    /**
     * @param string $message
     *
     * @return GpioEvent
     */
    public function decode(string $message): GpioEvent
    {
        $data = @unpack('Sid/Sflags/Ltick/Llevel', $message);

        if (!is_array($data) || count($data) !== 4) {
            throw new DecodingFailedException('Unable to unpack data. (Message: ' . bin2hex($message) . ')');
        }

        $pinStatus = $this->decodePinStatus($data);

        if ($data['flags'] & self::PI_NTFY_FLAGS_ALIVE) {
            $event = new AliveEvent($data['id'], $data['tick'], $pinStatus);
        } elseif ($data['flags'] & self::PI_NTFY_FLAGS_WDOG) {
            $eventId = $this->decodeEventFlag($data['flags']);
            $event = new WatchdogEvent($data['id'], $data['tick'], $pinStatus, $eventId);
        } elseif ($data['flags'] & self::PI_NTFY_FLAGS_EVENT) {
            $eventId = $this->decodeEventFlag($data['flags']);
            $event = new CustomEvent($data['id'], $data['tick'], $pinStatus, $eventId);
        } else {
            $event = new StateChangedEvent($data['id'], $data['tick'], $pinStatus);
        }

        return $event;
    }

    /**
     * @param array $message
     *
     * @return array
     */
    private function decodePinStatus(array $message): array
    {
        $pinStatus = [];
        for ($i = 1; $i < 32; $i++) {
            $pinStatus[] = new GpioStatus($i, $message['level'] & (1 << $i));
        }

        return $pinStatus;
    }

    /**
     * @param int $flags
     *
     * @return int
     */
    private function decodeEventFlag(int $flags): int
    {
        $result = 0;

        for ($i = 0; $i <= 4; $i++) {
            if ($flags & (1 << $i)) {
                $result += pow(2, $i);
            }
        }

        return $result;
    }
}