<?php

namespace Le\PDF417\Tests\Renderer;

use InvalidArgumentException;
use Le\PDF417\BarcodeData;
use Le\PDF417\Renderer\ImageRenderer;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\TestCase;

class ImageRendererTest extends TestCase
{
    public function testContentType()
    {
        $expected = 'image/png';

        $renderer = new ImageRenderer();
        $actual = $renderer->getContentType();
        $this->assertSame($expected, $actual);

        $renderer = new ImageRenderer(['format' => 'png']);
        $actual = $renderer->getContentType();
        $this->assertSame($expected, $actual);


        $renderer = new ImageRenderer(['format' => 'jpg']);
        $actual = $renderer->getContentType();
        $expected = 'image/jpeg';
        $this->assertSame($expected, $actual);

        $renderer = new ImageRenderer(['format' => 'gif']);
        $actual = $renderer->getContentType();
        $expected = 'image/gif';
        $this->assertSame($expected, $actual);

        $renderer = new ImageRenderer(['format' => 'bmp']);
        $actual = $renderer->getContentType();
        $expected = 'image/bmp';
        $this->assertSame($expected, $actual);

        $renderer = new ImageRenderer(['format' => 'tif']);
        $actual = $renderer->getContentType();
        $expected = 'image/tiff';
        $this->assertSame($expected, $actual);

        // data-url format does not have a mime type
        $renderer = new ImageRenderer(['format' => 'data-url']);
        $actual = $renderer->getContentType();
        $this->assertNull($actual);
    }

    public function testInvalidFormat()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid option "format": "foo".');
        new ImageRenderer(['format' => 'foo']);
    }

    public function testInvalidScale()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid option "scale": "0".');
        new ImageRenderer(['scale' => 0]);
    }

    public function testInvalidRatio()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid option "ratio": "0".');
        new ImageRenderer(['ratio' => 0]);
    }
    public function testInvalidPadding()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid option "padding": "-1".');
        new ImageRenderer(['padding' => -1]);
    }

    public function testInvalidColor()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid option "color": "red".');
        new ImageRenderer(['color' => 'red']);
    }

    public function testInvalidBgColor()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid option "bgColor": "red".');
        new ImageRenderer(['bgColor' => 'red']);
    }

    public function testInvalidQuality()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid option "quality": "101".');
        new ImageRenderer(['quality' => 101]);
    }

    public function testRenderPNG()
    {
        $data = new BarcodeData();
        $data->codes = [[true, false],[false, true]];

        $scale = 4;
        $ratio = 5;
        $padding = 6;

        $renderer = new ImageRenderer([
            'format' => 'png',
            'scale' => $scale,
            'ratio' => $ratio,
            'padding' => $padding
        ]);


        $png = $renderer->render($data);

        $manager = new ImageManager();
        $image = $manager->make($png);

        // Expected dimensions
        $width = 2 * $padding + 2 * $scale;
        $height = 2 * $padding + 2 * $scale * $ratio;
        $mime = 'image/png';

        $this->assertSame($width, $image->width());
        $this->assertSame($height, $image->height());
        $this->assertSame($mime, $image->mime);
    }


    public function testColors()
    {
        $color = '#ff0000';
        $bgColor = '#0000ff';

        $renderer = new ImageRenderer([
            'color' => $color,
            'bgColor' => $bgColor,
        ]);

        $data = new BarcodeData();
        $data->codes = [[true, false],[false, true]];

        $png = $renderer->render($data);

        // Open the image
        $manager = new ImageManager();
        $image = $manager->make($png);

        // The whole image should have either forground or background color
        // Check no other colors appear in the image
        for ($x = 0; $x < $image->width(); $x++) {
            for ($y = 0; $y < $image->height(); $y++) {
                $c = $image->pickColor($x, $y, 'hex');
                $this->assertTrue(
                    in_array($c, [$color, $bgColor]),
                    "Unexpected color $c encountered. Expected only $color or $bgColor."
                );
            }
        }
    }
}
