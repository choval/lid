<?php

namespace Choval;

class Lid
{
    /**
     * Constants
     */
    public const MODE_LID = 'L';
    public const SEPARATOR_LID = '-';
    public const MODE_WEB = 'W';
    public const SEPARATOR_WEB = '_';


    /**
     * Number
     */
    protected $number = false;
    protected $mode = '';



    /**
     * Bases
     */
    protected static $web_base = '0123456789CEFHJKNPVX';
    protected static $web_alias = [
        '0' => ['O', 'o', 'D', 'Q'],
        '1' => ['I', 'i', 'L', 'l'],
        '2' => ['Z', 'z'],
        '3' => [],
        '4' => ['A', 'Y', 'y'],
        '5' => ['S', 's'],
        '6' => ['G', 'b'],
        '7' => ['T'],
        '8' => ['B', 'R', 'g'],
        '9' => ['q'],
        'C' => ['c'],
        'E' => ['e'],
        'F' => ['f'],
        'H' => ['h'],
        'J' => ['j'],
        'K' => ['k'],
        'N' => ['n', 'M', 'm'],
        'P' => ['p'],
        'V' => ['v', 'U', 'u', 'W', 'w'],
        'X' => ['x'],
    ];
    protected static $id_base = '0123456789' . 'abcdefghijklmnopqrstuvwxyz' . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';



    /**
     * Constructor
     */
    public function __construct($id)
    {
        if (is_numeric($id)) {
            $this->number = $id;
        } elseif (is_a($id, self::class)) {
            $this->number = $id->number();
        } else {
            $this->number = static::parse($id, $this->mode);
        }
        if ($this->number === false) {
            throw new \Exception('Not a valid LID');
        }
    }



    /**
     * To string
     */
    public function __toString()
    {
        return $this->id();
    }



    /**
     * Returns the number
     */
    public function number()
    {
        return $this->number;
    }



    /**
     * Returns the ID
     */
    public function id()
    {
        return static::convert($this->number, static::MODE_LID);
    }


  
    /**
     * Alias of id
     */
    public function lid()
    {
        return $this->id();
    }



    /**
     * Returns the MODE_WEB version
     */
    public function web(bool $checksum = false)
    {
        return static::convert($this->number, static::MODE_WEB);
    }



    /**
     * Converts from a number to an IdHash
     */
    public static function convert(int $id, string $mode = null)
    {
        if (is_null($mode)) {
            $mode = static::MODE_LID;
        }
        $checkdigit = damm_digit($id);
        switch ($mode) {
            case static::MODE_LID:
                $max = 12;
                $sep = static::SEPARATOR_LID;
                $base = static::$id_base;
                break;
            case static::MODE_WEB:
                $max = 16;
                $sep = static::SEPARATOR_WEB;
                $base = static::$web_base;
                break;
            default:
                return false;
        }
        $s = static::toBase($id, $base);
        $s = str_pad($s, $max, '0', STR_PAD_LEFT);
        // $s = $checkdigit . $s;
        $parts = str_split($s, 4);
        foreach ($parts as $pos => $part) {
            if ($part === '0000') {
                $parts[$pos] = '0';
                continue;
            }
            $part = str_split($part);
            while (current($part) === '0') {
                array_shift($part);
            }
            $parts[$pos] = implode($part);
        }
        if ($parts[0] == '0') {
            $parts[0] = $checkdigit;
        } else {
            $parts[0] = $checkdigit . substr($parts[0], 0, 3);
        }
        $s = implode($sep, $parts);
        switch ($mode) {
            case static::MODE_LID:
                $s = str_replace($sep . '0' . $sep, $sep, $s);
                break;
            case static::MODE_WEB:
                $s = str_replace($sep . '0' . $sep . '0' . $sep, $sep, $s);
                $s = preg_replace('/^([0-9])' . $sep . '0' . $sep . '/', '$1' . $sep, $s);
                break;
        }
        return $s;
    }



    /**
     * Converts from an IdHash to a number
     */
    public static function parse(string $id, string &$mode = '')
    {
        if (is_numeric($id)) {
            return (int)$id;
        }
        $len = strlen($id);
        $isLid = strpos($id, static::SEPARATOR_LID);
        $isWeb = strpos($id, static::SEPARATOR_WEB);
        $mode = false;
        if ($isLid) {
            $mode = static::MODE_LID;
        } elseif ($isWeb) {
            $mode = static::MODE_WEB;
        } elseif ($isLid === false && $isWeb === false) {
            if ($len == 12) {
                $mode = static::MODE_LID;
            } elseif ($len == 16) {
                $mode = static::MODE_WEB;
            }
        }
        switch ($mode) {
            case static::MODE_LID:
                $sep = static::SEPARATOR_LID;
                $fill = 3;
                break;
            case static::MODE_WEB:
                $sep = static::SEPARATOR_WEB;
                $fill = 4;
                break;
        }
        $parts = explode($sep, $id);
        $first_part = array_shift($parts);
        foreach ($parts as $pos => $part) {
            $parts[$pos] = str_pad($part, 4, '0', STR_PAD_LEFT);
        }
        $till = $fill - 1;
        while (count($parts) < $till) {
            array_unshift($parts, '0000');
        }
        if (strlen($first_part) === 1) {
            $first_part .= '000';
        } else {
            $first_part = substr($first_part, 0, 1) . str_pad(substr($first_part, 1), 3, '0', STR_PAD_LEFT);
        }
        array_unshift($parts, $first_part);
        $id = implode($sep, $parts);
        $id = str_replace($sep, '', $id);
        $number = false;
        $base = static::$id_base;
        if ($mode == static::MODE_WEB) {
            foreach (static::$web_alias as $k => $vs) {
                $id = str_replace($vs, $k, $id);
            }
            $base = static::$web_base;
        }
        $checkdigit = substr($id, 0, 1);
        $id = substr($id, 1);
        $number = static::toInt($id, $base);
        if (damm_valid($number, $checkdigit)) {
            return $number;
        }
        return false;
    }



    /**
     * Convert to base
     */
    public static function toBase(int $number, string $base): string
    {
        return base_convert($number, 10, $base);
    }



    /**
     * Convert to int
     */
    public static function toInt(string $id, string $base)
    {
        return base_convert($id, $base, 10);
    }
}
