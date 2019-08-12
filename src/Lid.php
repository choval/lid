<?php
namespace Choval;

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
  public function hid() {
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
    $checksum = static::checksum($id);
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
      $s = $checksum.$s;
    } else {
      $i = substr($s, 0, 1);
      $s = $checksum.substr($s, 1);
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
    $checksum = false;
    $number = false;
    switch($mode) {
      case static::MODE_BASE:
        $checksum = substr($id, 0, 1);
        $id = substr($id, 1); 
        $number = static::toInt($id, static::$id_base);
        $check = static::checksum($number);
        if($check != $checksum) {
          return false;
        }
        break;
      case static::MODE_WEB:
        foreach(static::$web_alias as $k=>$vs) {
          $id = str_replace($vs, $k, $id);
        }
        $checksum = substr($id, 0, 1);
        $id = substr($id, 1);
        $number = static::toInt($id, static::$web_base);
        $check = static::checksum($number);
        if($check != $checksum) {
          return false;
        }
        break;
    }
    return $number;
  }



  /**
   * Convert to base
   */
  public static function toBase(int $number, string $base) : string {
    return static::convBase($number, '0123456789', $base);
  }



  /**
   * Convert to int
   */
  public static function toInt(string $id, string $base) {
    return static::convBase($id, $base, '0123456789');
  }



  /**
   * Implemented from https://github.com/tdely/luhn-php/blob/master/src/Luhn.php
   */
  protected static function algorithm(int $number) : int {
    $sum = 0;
    $parity = 1;
    $raw = (string)$number;
    $len = strlen($raw);
    for($i=$len-1;$i>=0;$i--) {
      $factor = $parity ? 2: 1;
      $parity = !$parity;
      $sum += array_sum(str_split($raw[$i] * $factor));
    }
    return $sum;
  }



  /**
   * Implemented from https://github.com/tdely/luhn-php/blob/master/src/Luhn.php
   */
  public static function checksum(int $number) : int {
    return ((static::algorithm($number) * 9) % 10);
  }




  /**
   * From https://gist.github.com/macik/4758146
   */
  public static function convBase($numberInput, $fromBaseInput, $toBaseInput)
  {
    if($fromBaseInput == $toBaseInput) {
      return $numberInput;
    }
    $fromBase = str_split($fromBaseInput,1);
    $toBase = str_split($toBaseInput,1);
    $number = str_split($numberInput,1);
    $fromLen=strlen($fromBaseInput);
    $toLen=strlen($toBaseInput);
    $numberLen=strlen($numberInput);
    $retval='';
    if($toBaseInput == '0123456789') {
      $retval=0;
      for($i = 1;$i <= $numberLen; $i++) {
        $retval = bcadd($retval, bcmul(array_search($number[$i-1], $fromBase),bcpow($fromLen,$numberLen-$i)));
      }
      return $retval;
    }
    if($fromBaseInput != '0123456789') {
      $base10=convBase($numberInput, $fromBaseInput, '0123456789');
    } else {
      $base10 = $numberInput;
    }
    if($base10<strlen($toBaseInput)) {
      return $toBase[$base10];
    }
    while($base10 != '0') {
      $retval = $toBase[bcmod($base10,$toLen)].$retval;
      $base10 = bcdiv($base10,$toLen,0);
    }
    return $retval;
  }



}
