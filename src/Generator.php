<?php

namespace Chrd;

class Generator
{
    private $colorType = 9;
    private $header = 'chr$';

    public function generate($width, $height, $pixels, $attributes)
    {
        $charWidth = $width / 8;
        $charHeight = $height / 8;

        $bytes = $this->header;
        $pointer = strlen($this->header);

        $bytes[$pointer] = chr((int)$charWidth);
        $pointer++;
        $bytes[$pointer] =  chr((int)$charHeight);
        $pointer++;
        $bytes[$pointer] = chr((int)$this->colorType);
        $pointer++;

        for ($charY = 0; $charY < $charHeight; $charY++) {
            for ($charX = 0; $charX < $charWidth; $charX++) {
                for ($rowsCounter = 0; $rowsCounter < 8; $rowsCounter++) {
                    $byteText = '';
                    for ($bitsCounter = 0; $bitsCounter < 8; $bitsCounter++) {
                        $byteText .= $pixels[$charY * 8 + $rowsCounter][$charX * 8 + $bitsCounter];
                    }
                    $bytes[$pointer] = chr((int)bindec($byteText));
                    $pointer++;
                }
                $bytes[$pointer] = chr((int)bindec($attributes[$charY][$charX]));
                $pointer++;
            }
        }
        return $bytes;
    }
}