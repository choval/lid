# LID

LID (LongID) is a 64-bit number used to identify rows in a database.  
Made specifically for use with BIGINT/LONGINT in databases. Includes a checksum to verify data.

## Install

```sh
composer require choval/lid
```

## Format

It is built using a base 62:

```
0123456789
abcdefghijklmnopqrstuvwxyz
ABCDEFGHIJKLMNOPQRSTUVWXYZ
```

* Except the web format, see <a href="#web">Web</a>.

The checksum is in the first character.

### Examples

```
NUM: 9223372036854775807
WEB: 75FE-KXVC-3KT2-6XC7
LID: 7aZl:8N0y:58M7

NUM: 8057322199735401981
WEB: 94V7-27T8-8CT6-54X1
LID: 99Bc:CvJ3:BrCR

NUM: 495653535173419733
WEB: 9061-03H6-471H-T96H
LID: 90AC:66ri:lnk9

NUM: 21398
WEB: 1000-0000-0000-2H9V
LID: 1000:0000:05z8
```


<a name="web"></a>
## Web

A different base is used for the web format.

Altough longer in length due to a shorter base, it is easier for dictation (across dialects) and more error friendly for OCR engines.

Alias characters are replaced.

```
0 - zero - alias: OoDQ
1 - one - alias: Iil
2 - two - alias: Zz
3 - three
4 - four - alias: AYy
5 - five - alias: Ss
6 - six - alias: Gbh
7 - seven - alias: 
8 - eight - alias: BRg
9 - nine - alias: q
C - charlie
E - echo
F - foxtrot
H - hotel
J - juliett
K - kilo
N - november - alias: Mm
T - tango - alias: Pp
V - victor - alias: UuWw
X - xray
```

## Usage

```php
/**
 * Basic usage
 */
$lid = new Lid( 21398 );
echo $lid->id();
// 1000:0000:05z8
echo $lid->web();
// 1000-0000-0000-2H9V
echo $lid->number();
// 21398

/**
 * The constructor accepts an int, a LID or a Web LID
 */
$lid = new Lid( '1000-0000-0000-2H9V' );
echo $lid->id();
// 1000:0000:05z8
echo $lid->web();
// 1000-0000-0000-2H9V
echo $lid->number();
// 21398

/**
 * Notice how we pass characters not in the base.
 */
$lid = new Lid( '1ooo-oOoo-ooo0-zHqU' );
echo $lid->id();
// 1000:0000:05z8
echo $lid->web();
// 1000-0000-0000-2H9V
echo $lid->number();
// 21398
```

## License

MIT, see LICENSE

