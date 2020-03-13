# LID

LID (LongID) is a 64-bit number used to identify rows in a database.  
Made specifically for use with BIGINT/LONGINT in databases. Includes a checksum to verify data.

Note: v1.x is not compatible with v0.x.

## Install

```sh
composer require choval/lid^1.0
```

## Format

It is built using a base 62:

```
0123456789
abcdefghijklmnopqrstuvwxyz
ABCDEFGHIJKLMNOPQRSTUVWXYZ
```

* Except the web format, see <a href="#web">Web</a>.

The checksum (Damm) is the first character.

### Examples

```
NUM: 9223372036854775807
WEB: 75FE_KXVC_3KP2_6XC7
LID: 7aZl-8N0y-58M7

NUM: 8057322199735401981
WEB: 94V7_27P8_8CP6_54X1
LID: 99Bc-CvJ3-BrCR

NUM: 495653535173419733
WEB: 961_3H6_471H_P96H
LID: 9AC-66ri-lnk9

NUM: 3412381023195
WEB: 0_6H_5V91_7PXK
LID: 0-Y4L-x15V

NUM: 8954382094
WEB: 2_6XV4_PK4J
LID: 2-9L-ZFPE

NUM: 21398
WEB: 1_2H9V
LID: 1-5z8

NUM: 1024
WEB: 4_2E4
LID: 4-gw
```


<a name="web"></a>
## Web

A different base is used for the web format.

Altough longer in length due to a shorter base, it is easier for dictation (across dialects) and more error friendly for OCR engines.

Alias characters are replaced.

```
0 - zero - alias: OoDQ
1 - one - alias: IiLl
2 - two - alias: Zz
3 - three
4 - four - alias: AYy
5 - five - alias: Ss
6 - six - alias: Gb
7 - seven - alias: T
8 - eight - alias: BRg
9 - nine - alias: q
C - charlie - alias c
E - echo - alias e
F - foxtrot - alias f
H - hotel - alias h
J - juliett - alias j
K - kilo - alias k
N - november - alias: Mmn
P - tango - alias: p
V - victor - alias: UuWw
X - xray - alias x
```

## Usage

```php
/**
 * Basic usage
 */
$lid = new Lid( 21398 );
echo $lid->id();
// 1-5z8
echo $lid->web();
// 1_2H9V
echo $lid->number();
// 21398

/**
 * The constructor accepts an int, a LID or a Web LID
 */
$lid = new Lid( '1_2H9V' );
echo $lid->id();
// 1-5z8
echo $lid->web();
// 1_2H9V
echo $lid->number();
// 21398


/**
 * The constructor accepts long formats as well
 */
$lid = new Lid( '1000_0000_0000_2H9V' );
echo $lid->id();
// 1-5z8
echo $lid->web();
// 1_2H9V
echo $lid->number();
// 21398


/**
 * And the web format allows you to make mistakes with letters and nums
 */
$lid = new Lid( '1_zHqU' );
echo $lid->id();
// 1-5z8
echo $lid->web();
// 1_2H9V
echo $lid->number();
// 21398
```

## License

MIT, see LICENSE

