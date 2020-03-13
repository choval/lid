<?php

use Choval\Lid;

use PHPUnit\Framework\TestCase;

class LidTest extends TestCase
{
    public function randomNumber($max = 65536)
    {
        return random_int(0, $max);
    }
    public function randomSetLarge()
    {
        $out = [];
        for ($i = 0;$i < 100;$i++) {
            $out[] = [ $this->randomNumber(PHP_INT_MAX) ];
        }
        $out[] = [PHP_INT_MAX];
        return $out;
    }
    public function randomSetSmall()
    {
        $out = [];
        $out[] = [ 0 ];
        $out[] = [ 16 ];
        for ($i = 0;$i < 100;$i++) {
            $out[] = [ $this->randomNumber() ];
        }
        return $out;
    }



    /**
     * @dataProvider randomSetSmall
     */
    public function testToBase($num)
    {
        $base = '0123456789abcdef';
        $ahex = base_convert($num, 10, 16);
        $bhex = Lid::toBase($num, $base);
        $this->assertEquals($ahex, $bhex);
    }


    /**
     * @dataProvider randomSetLarge
     */
    public function testConvertAndParse($num)
    {
        $lid = Lid::convert($num);
        var_dump($lid);
        $chk = Lid::parse($lid);
        $this->assertEquals($num, $chk);
    }


    /**
     * @dataProvider randomSetSmall
     */
    public function testObj($num)
    {
        $obj = new Lid($num);
        $lid = $obj->lid();
        $web = $obj->web();
        echo "NUM: $num\nWEB: $web\nLID: $lid\n";
        $this->assertEquals($num, Lid::parse($web));
        $this->assertEquals($num, Lid::parse($lid));
    }


    /**
     * @dataProvider randomSetLarge
     */
    public function testObjLarge($num)
    {
        $obj = new Lid($num);
        $lid = $obj->lid();
        $web = $obj->web();
        echo "NUM: $num\nWEB: $web\nLID: $lid\n";
        $this->assertEquals($num, Lid::parse($web));
        $this->assertEquals($num, Lid::parse($lid));
    }


    /**
     * @dataProvider randomSetLarge
     */
    public function testUrlBadChars($num)
    {
        $obj = new Lid($num);
        $web = $obj->web();
        $web = str_replace('0', 'o', $web);
        $web = str_replace('1', 'i', $web);
        $web = str_replace('2', 'z', $web);
        $web = str_replace('4', 'A', $web);
        $web = str_replace('5', 'S', $web);
        $web = str_replace('6', 'G', $web);
        $this->assertEquals($num, Lid::parse($web));
    }


    /**
     * @dataProvider randomSetLarge
     */
    public function testCast($num)
    {
        $obj = new Lid($num);
        $test = $obj->number();
        $this->assertEquals($num, $test);
    }
}
