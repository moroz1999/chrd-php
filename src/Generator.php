<?php

namespace Chrd;

class Generator
{
    private $colorType = 9;
    private const HEADER = 'chr$';

    public function generate(int $width, int $height, array $pixels, array $attributes)
    {
        $charWidth = (int)($width / 8);
        $charHeight = (int)($height / 8);
        $header = self::HEADER . pack('C3', $charWidth, $charHeight, $this->colorType);
        $output = [];
        for ($charY = 0; $charY < $charHeight; $charY++) {
            for ($charX = 0; $charX < $charWidth; $charX++) {
                for ($row = 0; $row < 8; $row++) {
                    $y = $charY * 8 + $row;
                    $byte = 0;
                    for ($bit = 0; $bit < 8; $bit++) {
                        $x = $charX * 8 + $bit;
                        $byte = ($byte << 1) | (int)$pixels[$y][$x];
                    }
                    $output[] = chr($byte);
                }
                $attrByte = 0;
                $attrStr = $attributes[$charY][$charX];
                for ($i = 0; $i < 8; $i++) {
                    $attrByte = ($attrByte << 1) | (int)$attrStr[$i];
                }
                $output[] = chr($attrByte);
            }
        }
        return $header . implode('', $output);
    }
}
