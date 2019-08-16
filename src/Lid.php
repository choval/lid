<?php
namespace Choval;

use function Choval\base_convert;
use function Choval\damm_digit;
use function Choval\damm_valid;

class Lid {


  /**
   * Constants
   */
  const LID = 'L';
  const MODE_BASE = 'L';
  const MODE_WEB = 'W';


  /**
   * Number
   */
  protected $number = false;
  protected $mode = '';



  /**
   * Bases
   */
  protected static $web_base = '0123456789CEFHJKNTVX';
  protected static $web_alias = [
    '0' => ['O','o','D','Q'],
    '1' => ['I','i','L','l'],
    '2' => ['Z','z'],
    '3' => [],
    '4' => ['A','Y','y'],
    '5' => ['S','s'],
    '6' => ['G','b',],
    '7' => [],
    '8' => ['B','R','g'],
    '9' => ['q'],
    'C' => ['c'],
    'E' => ['e'],
    'F' => ['f'],
    'H' => ['h'],
    'J' => ['j'],
    'K' => ['k',],
    'N' => ['n','M','m'],
    'T' => ['t','P','p'],
    'V' => ['v','U','u','W','w'],
    'X' => ['x'],
  ];
  protected static $id_base = '0123456789'.'abcdefghijklmnopqrstuvwxyz'.'ABCDEFGHIJKLMNOPQRSTUVWXYZ';



  /**
   * Constructor
   */
  public function __construct($id) {
    if(is_numeric($id)) {
      $this->number = $id;
    } else if(is_a($id, self::class)) {
      $this->number = $id->number();
    } else {
      $this->number = static::parse($id, $this->mode);
    }
    if($this->number === false) {
      throw new \Exception('Not a valid LID');
    }
  }



  /**
   * To string
   */
  public function __toString() {
    return $this->id();
  }



  /**
   * Returns the number
   */
  public function number() {
    return $this->number;
  }



  /**
   * Returns the ID
   */
  public function id() {
    return static::convert($this->number, static::MODE_BASE);
  }


  
  /**
   * Alias of id
   */
  public function lid() {
    return $this->id();
  }



  /**
   * Returns the MODE_WEB version
   */
  public function web(bool $checksum=false) {
    return static::convert($this->number, static::MODE_WEB);
  }



  /**
   * Converts from a number to an IdHash
   */
  public static function convert(int $id, string $mode=null) {
    if(is_null($mode)) {
      $mode = static::MODE_BASE;
    }
    $checkdigit = damm_digit($id);
    switch($mode) {
      case static::MODE_BASE:
        $max = 11;
        $sep = ':';
        $base = static::$id_base;
        break;
      case static::MODE_WEB:
        $max = 16;
        $sep = '-';
        $base = static::$web_base;
        break;
      default:
        return false;
    }
    $s = static::toBase($id, $base);
    $s = str_pad($s, $max, '0', STR_PAD_LEFT);
    if($mode == static::MODE_BASE) {
      $s = $checkdigit.$s;
    } else {
      $i = substr($s, 0, 1);
      $s = $checkdigit.substr($s, 1);
    }
    $s = implode($sep, str_split($s, 4));
    return $s;
  }



  /**
   * Converts from an IdHash to a number
   */
  public static function parse(string $id, string &$mode='') {
    if(is_numeric($id)) {
      return (int)$id;
    }
    $len = strlen($id);
    $hasColon = strpos($id, ':');
    $hasMinus = strpos($id, '-');
    $mode = false;
    if($len == 14 && $hasColon) {
      $mode = static::MODE_BASE;
    }
    else if($len == 19 && $hasMinus) {
      $mode = static::MODE_WEB;
    }
    else if($hasColon === false && $hasMinus === false) {
      if($len == 12) {
        $mode = static::MODE_BASE;
      }
      else if($len == 16) {
        $mode = static::MODE_WEB;
      }
    }
    $id = str_replace(['-',':'],'',$id);
    $number = false;
    $base = static::$id_base;
    if($mode == static::MODE_WEB) {
      foreach(static::$web_alias as $k=>$vs) {
        $id = str_replace($vs, $k, $id);
      }
      $base = static::$web_base;
    }
    $checkdigit = substr($id, 0, 1);
    $id = substr($id, 1); 
    $number = static::toInt($id, $base);
    if( damm_valid($number, $checkdigit) ) {
      return $number;
    }
    return false;
  }



  /**
   * Convert to base
   */
  protected static function toBase(int $number, string $base) : string {
    return base_convert($number, 10, $base);
  }



  /**
   * Convert to int
   */
  protected static function toInt(string $id, string $base) {
    return base_convert($id, $base, 10);
  }



}


