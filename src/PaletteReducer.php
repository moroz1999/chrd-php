<?php

namespace Chrd;

class PaletteReducer
{
    private $zxPalette = [
        '0000' => 0x000000,
        '0001' => 0x0000CD,
        '0010' => 0xCD0000,
        '0011' => 0xCD00CD,
        '0100' => 0x00CD00,
        '0101' => 0x00CDCD,
        '0110' => 0xCDCD00,
        '0111' => 0xCDCDCD,

        '1000' => 0x000000,
        '1001' => 0x0000FF,
        '1010' => 0xFF0000,
        '1011' => 0xFF00FF,
        '1100' => 0x00FF00,
        '1101' => 0x00FFFF,
        '1110' => 0xFFFF00,
        '1111' => 0xFFFFFF,
    ];
    private $pairColors = [
        '0000' => '1000',
        '0001' => '1001',
        '0010' => '1010',
        '0011' => '1011',
        '0100' => '1100',
        '0101' => '1101',
        '0110' => '1110',
        '0111' => '1111',

        '1000' => '0000',
        '1001' => '0001',
        '1010' => '0010',
        '1011' => '0011',
        '1100' => '0100',
        '1101' => '0101',
        '1110' => '0110',
        '1111' => '0111',
    ];
    private $imageData;
    private $resultImageData;
    public $pixelsData = [];
    public $attributesData = [];

    public function __construct()
    {
    }

    public function reducePalette($imageData, $resultImageData)
    {
        $this->imageData = $imageData;
        $this->resultImageData = $resultImageData;
        $this->pixelsData = [];
        $this->attributesData = [];

        $width = ceil(imagesx($imageData) / 8);
        $height = ceil(imagesy($imageData) / 8);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $this->processAttribute($x, $y);
            }
        }
        return $resultImageData;
    }

    private function processAttribute($attrX, $attrY)
    {
        if (!$this->imageData || !$this->resultImageData) {
            return;
        }

        $colors = [];
        for ($y = $attrY * 8; $y < $attrY * 8 + 8; $y++) {
            for ($x = $attrX * 8; $x < $attrX * 8 + 8;
                 $x++) {
                $color = $this->imageColorAt($this->imageData, $x, $y);
                $colors[] = [
                    'color' => $color,
                    'x' => $x,
                    'y' => $y,
                ];
            }
        }

        $usedColorsIndex = [];
        foreach ($colors as $info) {
            $closest = [];

            foreach ($this->zxPalette as $code => $color) {
                $diff = $this->colorDiff($info['color'], $color);
                if (!isset($closest['diff']) || $closest['diff'] > $diff) {
                    $closest['diff'] = $diff;
                    $closest['color'] = $color;
                    $closest['code'] = $code;
                }
            }
            if (isset($closest['code'])) {
                if (!isset($usedColorsIndex[$closest['code']])) {
                    $usedColorsIndex[$closest['code']] = 0;
                }
                $usedColorsIndex[$closest['code']]++;
            }
        }
        $usedColors = [];
        foreach ($usedColorsIndex as $code => $count) {
            $usedColors[] = ['code' => $code, 'count' => $count];
        }

        usort($usedColors, function ($a, $b) {
            return count($b) - count($a);
        });
        $minColor = null;
        $maxColor = array_shift($usedColors)['code'];
        if (count($usedColors) > 0) {
            $minColor = array_shift($usedColors)['code'];
            if ($maxColor && $this->pairColors[$maxColor] == $minColor) {
                if (count($usedColors) > 0) {
                    $minColor = array_shift($usedColors)['code'];
                } else {
                    $minColor = $maxColor;
                }
            }
        } else {
            $minColor = $maxColor;
        }
        foreach ($colors as $i => $info) {
            if ($this->colorDiff($info['color'], $this->zxPalette[$maxColor]) < $this->colorDiff($info['color'], $this->zxPalette[$minColor])) {
                $this->imageSetPixel($this->resultImageData, $info['x'], $info['y'], $this->zxPalette[$maxColor]);
                $this->setChrdPixel(true, $info['x'], $info['y'], $maxColor);
            } else {
                $this->imageSetPixel($this->resultImageData, $info['x'], $info['y'], $this->zxPalette[$minColor]);
                $this->setChrdPixel(false, $info['x'], $info['y'], $minColor);
            }
        }
    }

    private function setChrdPixel($ink, $x, $y, $colorCode)
    {
        if (!isset($this->pixelsData[$y])) {
            $this->pixelsData[$y] = [];
        }
        $this->pixelsData[$y][$x] = $ink ? 1 : 0;

        $attrX = floor($x / 8);
        $attrY = floor($y / 8);
        if (!isset($this->attributesData[$attrY])) {
            $this->attributesData[$attrY] = [];
        }
        $attribute = '00000000';
        if (isset($this->attributesData[$attrY][$attrX])) {
            $attribute = $this->attributesData[$attrY][$attrX];
        }

        if ($colorCode === '0000' || $colorCode === '1000') {
            $brightness = substr($attribute, 1, 1);
        } else {
            $brightness = substr($colorCode, 0, 1);
        }

        if ($ink) {
            $attribute = '0' . $brightness . substr($attribute, 2, 3) . substr($colorCode, 1, 3);
        } else {
            $attribute = '0' . $brightness . substr($colorCode, 1, 3) . substr($attribute, 5, 3);
        }
        $this->attributesData[$attrY][$attrX] = $attribute;
    }

    private function imageColorAt($imageData, $x, $y)
    {
        return imagecolorat($imageData, $x, $y);
    }

    private function imageSetPixel($imageData, $x, $y, $color)
    {
        imagesetpixel($imageData, $x, $y, $color);
    }

    private function colorDiff($rgb, $rgb2)
    {
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        $r2 = ($rgb2 >> 16) & 0xFF;
        $g2 = ($rgb2 >> 8) & 0xFF;
        $b2 = $rgb2 & 0xFF;

        return $this->simpleDiff($r, $g, $b, $r2, $g2, $b2);
    }

    private function simpleDiff($R1, $G1, $B1, $R2, $G2, $B2)
    {
        $rMean = ($R1 + $R2) / 2;
        $r = $R1 - $R2;
        $g = $G1 - $G2;
        $b = $B1 - $B2;

        $weightR = 2 + $rMean / 256;
        $weightG = 4.0;
        $weightB = 2 + (255 - $rMean) / 256;
        return sqrt($weightR * $r * $r + $weightG * $g * $g + $weightB * $b * $b);
    }
}
