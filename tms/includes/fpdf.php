<?php
/**
 * Minimal FPDF class – placeholder for demo.
 * Replace with full FPDF library from http://www.fpdf.org for real PDF output.
 */
class FPDF {
    protected $buffer = '';
    protected $x, $y, $marginLeft, $marginTop;

    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {
        $this->marginLeft = 10;
        $this->marginTop = 10;
        $this->x = $this->marginLeft;
        $this->y = $this->marginTop;
    }

    public function AddPage() {}
    public function SetFont($family, $style = '', $size = 12) {}
    public function SetXY($x, $y) { $this->x = $x; $this->y = $y; }

    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = 'L') {
        $this->buffer .= "Cell: $txt\n";
        $this->x += $w;
        if ($ln) {
            $this->y += $h ?: 5;
            $this->x = $this->marginLeft;
        }
    }

    public function MultiCell($w, $h, $txt, $border = 0, $align = 'L') {
        $this->buffer .= "MultiCell: $txt\n";
        $this->y += $h;
        $this->x = $this->marginLeft;
    }

    public function Ln($h = null) {
        $this->y += $h ?: 5;
        $this->x = $this->marginLeft;
    }

    public function Output($dest = 'I', $name = '') {
        // Clear any output buffer to avoid "headers already sent" errors
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set proper headers if they haven't been sent yet
        if (!headers_sent()) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $name . '"');
        }

        // For demonstration: output the buffered text as plain text
        // Replace this with a real PDF generation method when using full FPDF library
        echo $this->buffer;
        exit;
    }

    public function SetMargins($left, $top, $right = -1) {}
    public function SetAutoPageBreak($auto, $margin = 0) {}
}
?>