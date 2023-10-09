<?php

namespace Src\Components;

use Exception;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorJPG;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Picqer\Barcode\BarcodeGeneratorDynamicHTML;

class Barcode 
{
    protected $barcodeHTML;
    protected $barcodePNG;
    protected $barcodeJPG;
    protected $barcodeSVG;
    protected $barcodeDynamicHTML;

    public function __construct(array $config = []) 
    {
        $this->barcodeHTML = new BarcodeGeneratorHTML();
        $this->barcodePNG = new BarcodeGeneratorPNG();
        $this->barcodeJPG = new BarcodeGeneratorJPG();
        $this->barcodeSVG = new BarcodeGeneratorSVG();
        $this->barcodeDynamicHTML = new BarcodeGeneratorDynamicHTML();
    }

    public function getBarcodeHTML(string $data): ?string
    {
        try {
            return $this->barcodeHTML->getBarcode($data, $this->barcodeHTML::TYPE_EAN_2);
        } catch(Exception $e) {
            $this->error = $e;
        }

        return null;
    }

    public function getBarcodePNG(string $data): ?string 
    {
        try {
            return '<img src="data:image/png;base64,' 
                . base64_encode($this->barcodePNG->getBarcode($data, $this->barcodePNG::TYPE_EAN_2)) 
                . '">';
        } catch(Exception $e) {
            $this->error = $e;
        }

        return null;
    }

    public function error(): ?Exception 
    {
        return $this->error;
    }
}