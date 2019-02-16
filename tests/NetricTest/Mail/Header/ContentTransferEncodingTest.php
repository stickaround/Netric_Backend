<?php
namespace NetricTest\Mail\Header;

use Netric\Mail\Header\ContentTransferEncoding;
use Netric\Mail\Header\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @group      Netric_Mail
 */
class ContentTransferEncodingTest extends TestCase
{
    public function dataValidEncodings()
    {
        return [
            ['7bit'],
            ['8bit'],
            ['binary'],
            ['quoted-printable'],
        ];
    }

    public function dataInvalidEncodings()
    {
        return [
            ['9bit'],
            ['x-something'],
        ];
    }

    /**
     * @dataProvider dataValidEncodings
     */
    public function testContentTransferEncodingFromStringCreatesValidContentTransferEncodingHeader($encoding)
    {
        $contentTransferEncodingHeader = ContentTransferEncoding::fromString('Content-Transfer-Encoding: '.$encoding);
        $this->assertInstanceOf('Netric\Mail\Header\HeaderInterface', $contentTransferEncodingHeader);
        $this->assertInstanceOf('Netric\Mail\Header\ContentTransferEncoding', $contentTransferEncodingHeader);
    }

    /**
     * @dataProvider dataInvalidEncodings
     */
    public function testContentTransferEncodingFromStringRaisesException($encoding)
    {
        $this->expectException('Netric\Mail\Header\Exception\InvalidArgumentException');
        $contentTransferEncodingHeader = ContentTransferEncoding::fromString('Content-Transfer-Encoding: '.$encoding);
    }

    public function testContentTransferEncodingGetFieldNameReturnsHeaderName()
    {
        $contentTransferEncodingHeader = new ContentTransferEncoding();
        $this->assertEquals('Content-Transfer-Encoding', $contentTransferEncodingHeader->getFieldName());
    }

    /**
     * @dataProvider dataValidEncodings
     */
    public function testContentTransferEncodingGetFieldValueReturnsProperValue($encoding)
    {
        $contentTransferEncodingHeader = new ContentTransferEncoding();
        $contentTransferEncodingHeader->setTransferEncoding($encoding);
        $this->assertEquals($encoding, $contentTransferEncodingHeader->getFieldValue());
    }

    /**
     * @dataProvider dataValidEncodings
     */
    public function testContentTransferEncodingHandlesCaseInsensitivity($encoding)
    {
        $header = new ContentTransferEncoding();
        $header->setTransferEncoding(strtoupper(substr($encoding, 0, 4)).substr($encoding, 4));
        $this->assertEquals(strtolower($encoding), strtolower($header->getFieldValue()));
    }

    /**
     * @dataProvider dataValidEncodings
     */
    public function testContentTransferEncodingToStringReturnsHeaderFormattedString($encoding)
    {
        $contentTransferEncodingHeader = new ContentTransferEncoding();
        $contentTransferEncodingHeader->setTransferEncoding($encoding);
        $this->assertEquals("Content-Transfer-Encoding: ".$encoding, $contentTransferEncodingHeader->toString());
    }

    public function testProvidingParametersIntroducesHeaderFolding()
    {
        $header = new ContentTransferEncoding();
        $header->setTransferEncoding('quoted-printable');
        $string = $header->toString();

        $this->assertStringContainsString("Content-Transfer-Encoding: quoted-printable", $string);
    }

    /**
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionOnInvalidHeaderName()
    {
        $this->expectException('Netric\Mail\Header\Exception\InvalidArgumentException');
        ContentTransferEncoding::fromString('Content-Transfer-Encoding' . chr(32) . ': 8bit');
    }

    public function headerLines()
    {
        return [
            'newline' => ["Content-Transfer-Encoding: 8bit\n7bit"],
            'cr-lf' => ["Content-Transfer-Encoding: 8bit\r\n7bit"],
            'multiline' => ["Content-Transfer-Encoding: 8bit\r\n7bit\r\nUTF-8"],
        ];
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionForInvalidMultilineValues($headerLine)
    {
        $this->expectException(InvalidArgumentException::class);
        ContentTransferEncoding::fromString($headerLine);
    }

    /**
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionForContinuations()
    {
        $this->expectException(InvalidArgumentException::class);
        ContentTransferEncoding::fromString("Content-Transfer-Encoding: 8bit\r\n 7bit");
    }

    /**
     * @group ZF2015-04
     */
    public function testSetTransferEncodingRaisesExceptionForInvalidValues()
    {
        $header = new ContentTransferEncoding();
        $this->expectException(InvalidArgumentException::class);
        $header->setTransferEncoding("8bit\r\n 7bit");
    }
}
