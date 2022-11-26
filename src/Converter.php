<?php

namespace Chrd;

class Converter
{
    public function convert($source, $destination)
    {
        $imageData = $this->loadImage($source);
        $resultImageData = imagecreatetruecolor(imagesx($imageData), imagesy($imageData));

        imagecopy($resultImageData, $imageData, 0, 0, 0, 0, imagesx($imageData), imagesy($imageData));

        $paletteReducer = new PaletteReducer();
        $paletteReducer->reducePalette($imageData, $resultImageData);

        $chrdGenerator = new Generator();
        $bytes = $chrdGenerator->generate(imagesx($imageData), imagesy($imageData), $paletteReducer->pixelsData, $paletteReducer->attributesData);
        file_put_contents($destination, $bytes);
    }

    private function loadImage($source)
    {
        $image = imagecreatefrompng($source);
        if (!$image) {
            throw new \Exception('Invalid image ' . $source);
        }
        return $image;
    }
}