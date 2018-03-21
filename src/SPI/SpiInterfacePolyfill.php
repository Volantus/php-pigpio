<?php
namespace Volantus\BerrySpi;

if (!interface_exists('Volantus\BerrySpi\SpiInterface')) {
    /**
     * Interface SpiInterface
     *
     * @package Volantus\BerrySpi
     * @link https://github.com/Volantus/berry-spi
     */
    interface SpiInterface
    {
        /**
         * @return void
         */
        public function open();

        /**
         * @return void
         */
        public function close();

        /**
         * @return int
         */
        public function getSpeed(): int;

        /**
         * @return int
         */
        public function getFlags(): int;

        /**
         * @return bool
         */
        public function isOpen(): bool;

        /**
         * @param array $data
         *
         * @return array
         */
        public function transfer(array $data): array;

        /**
         * @return bool
         */
        public static function initialize() : bool;

        /**
         * @return bool
         */
        public static function isInitialized() : bool;
    }
}