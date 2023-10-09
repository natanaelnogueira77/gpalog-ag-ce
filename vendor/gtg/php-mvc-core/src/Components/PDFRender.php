<?php

namespace GTG\MVC\Components;

use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use GTG\MVC\Exceptions\AppException;
use stdClass;

class PDFRender 
{
    private ?Dompdf $dompdf = null;
    private ?Options $options = null;
    private stdClass $data;
    private ?Exception $error = null;

    public function __construct(?array $options = ['isRemoteEnabled' => true, 'isPhpEnabled' => true]) 
    {
        if($options) {
            $this->options = new Options();
            foreach($options as $option => $value) {
                $this->options->set($option, $value);
            }
        }

        $this->data = new stdClass();
    }

    public function setOptions(array $options): self
    {
        $this->options = new Options();
        foreach($options as $option => $value) {
            $this->options->set($option, $value);
        }
        return $this;
    }

    public function loadHtml(string $html = ''): self 
    {
        $this->data->html = $html;
        return $this;
    }

    public function setPaper(?string $size = 'A4', ?string $orientation = 'landscape'): self 
    {
        $this->data->paperSize = $size;
        $this->data->paperOrientation = $orientation;
        return $this;
    }

    public function render(): bool
    {
        try {
            $this->dompdf = new Dompdf($this->options);

            if(!$this->data->html) {
                throw new Exception('The HTML is needed!');
            }

            if(!$this->data->paperSize) {
                $this->data->paperSize = 'A4';
            }

            if(!$this->data->paperOrientation) {
                $this->data->paperOrientation = 'landscape';
            }

            $this->dompdf->loadHtml($this->data->html);
            $this->dompdf->setPaper($this->data->paperSize, $this->data->paperOrientation);
            $this->dompdf->render();

            return true;
        } catch(Exception $e) {
            $this->error = (new AppException($e->getMessage()));
            return false;
        }
    }

    public function stream(string $filename, array $options): void 
    {
        $this->dompdf->stream($filename, $options);
    }

    public function getDompdf(): Dompdf
    {
        return $this->dompdf;
    }

    public function error(): ?AppException 
    {
        return $this->error;
    }
}