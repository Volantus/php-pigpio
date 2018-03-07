<?php
/**
 * Created by PhpStorm.
 * User: marius
 * Date: 07.03.18
 * Time: 23:08
 */

namespace Volantus\Pigpio\Protocol;

/**
 * Class Commands
 *
 * @package Volantus\Pigpio\Protocol
 */
class Commands
{
    const MODES = 0;
    const MODEG = 1;
    const PUD = 2;
    const READ = 3;
    const WRITE = 4;
    const PWM = 5;
    const PRS = 6;
    const PFS = 7;
    const SERVO = 8;
    const WDOG = 9;
    const BR1 = 10;
    const BR2 = 11;
    const BC1 = 12;
    const BC2 = 13;
    const BS1 = 14;
    const BS2 = 15;
    const TICK = 16;
    const HWVER = 17;
    const NO = 18;
    const NB = 19;
    const NP = 20;
    const NC = 21;
    const PRG = 22;
    const PFG = 23;
    const PRRG = 24;
    const HELP = 25;
    const PIGPV = 26;
    const WVCLR = 27;
    const WVAG = 28;
    const WVAS = 29;
    const WVGO = 30;
    const WVGOR = 31;
    const WVBSY = 32;
    const WVHLT = 33;
    const WVSM = 34;
    const WVSP = 35;
    const WVSC = 36;
    const TRIG = 37;
    const PROC = 38;
    const PROCD = 39;
    const PROCR = 40;
    const PROCS = 41;
    const SLRO = 42;
    const SLR = 43;
    const SLRC = 44;
    const PROCP = 45;
    const MICS = 46;
    const MILS = 47;
    const PARSE = 48;
    const WVCRE = 49;
    const WVDEL = 50;
    const WVTX = 51;
    const WVTXR = 52;
    const WVNEW = 53;
    const I2CO = 54;
    const I2CC = 55;
    const I2CRD = 56;
    const I2CWD = 57;
    const I2CWQ = 58;
    const I2CRS = 59;
    const I2CWS = 60;
    const I2CRB = 61;
    const I2CWB = 62;
    const I2CRW = 63;
    const I2CWW = 64;
    const I2CRK = 65;
    const I2CWK = 66;
    const I2CRI = 67;
    const I2CWI = 68;
    const I2CPC = 69;
    const I2CPK = 70;
    const SPIO = 71;
    const SPIC = 72;
    const SPIR = 73;
    const SPIW = 74;
    const SPIX = 75;
    const SERO = 76;
    const SERC = 77;
    const SERRB = 78;
    const SERWB = 79;
    const SERR = 80;
    const SERW = 81;
    const SERDA = 82;
    const GDC = 83;
    const GPW = 84;
    const HC = 85;
    const HP = 86;
    const CF1 = 87;
    const CF2 = 88;
    const BI2CC = 89;
    const BI2CO = 90;
    const BI2CZ = 91;
    const I2CZ = 92;
    const WVCHA = 93;
    const SLRI = 94;
    const CGI = 95;
    const CSI = 96;
    const FG = 97;
    const FN = 98;
    const NOIB = 99;
    const WVTXM = 100;
    const WVTAT = 101;
    const PADS = 102;
    const PADG = 103;
    const FO = 104;
    const FC = 105;
    const FR = 106;
    const FW = 107;
    const FS = 108;
    const FL = 109;
    const SHELL = 110;
    const BSPIC = 111;
    const BSPIO = 112;
    const BSPIX = 113;
    const BSCX = 114;
    const EVM = 115;
    const EVT = 116;
    const PROCU = 117;

    private function __construct()
    {
    }
}