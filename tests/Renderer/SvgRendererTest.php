<?php

namespace Le\PDF417\Tests\Renderer;

use Le\PDF417\BarcodeData;
use Le\PDF417\Renderer\SvgRenderer;
use PHPUnit\Framework\TestCase;

class SvgRendererTest extends TestCase
{
    public function testContentType()
    {
        $renderer = new SvgRenderer();
        $actual = $renderer->getContentType();
        $expected = 'image/svg+xml';
        $this->assertSame($expected, $actual);
    }

    public function testRender()
    {
        $data = new BarcodeData();
        $data->codes = [[true, false],[false, true]];

        $scale = 3;
        $ratio = 5;

        $renderer = new SvgRenderer([
            'scale' => $scale,
            'ratio' => $ratio,
        ]);

        $string = $renderer->render($data);

        // Check it contains the correct doctype
        $doctype = '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">';
        $this->assertStringContainsString($doctype, $string);

        // Check document structure
        $xml = simplexml_load_string($string);
        $this->assertFalse(isset($xml->description));
        $this->assertTrue(isset($xml->g));

        foreach($xml->g as $group) {
            foreach($group->rect as $rect) {
                $this->assertSame($scale * $ratio, (int) $rect['height']);
                $this->assertSame($scale, (int) $rect['width']);
                $this->assertGreaterThanOrEqual(0, (int) $rect['x']);
                $this->assertGreaterThanOrEqual(0, (int) $rect['y']);
            }
        }
    }

    public function testRenderWithDescription()
    {
        $data = new BarcodeData();
        $data->codes = [[true, false],[false, true]];

        $desc = 'today is a good day to generate barcodes';

        $renderer = new SvgRenderer([
            'description' => $desc
        ]);

        $string = $renderer->render($data);

        // Check description exists
        $xml = simplexml_load_string($string);
        $this->assertTrue(isset($xml->description));
        $this->assertSame($desc, strval($xml->description));
    }
}
