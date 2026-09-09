<?php

namespace App\Support\Files;

use setasign\Fpdi\Fpdi;

final class SimplePdf
{
    public static function write(string $absolutePath, string $title, string $body): void
    {
        self::writePages($absolutePath, [$title.': '.$body]);
    }

    /** @param list<string> $lines */
    public static function writePages(string $absolutePath, array $lines): void
    {
        $dir = dirname($absolutePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $pdf = new Fpdi('P', 'mm', 'Letter');
        $pdf->SetAutoPageBreak(false);
        foreach ($lines as $line) {
            $pdf->AddPage();
            $pdf->SetFont('Helvetica', '', 14);
            $pdf->SetXY(20, 30);
            $latin = mb_convert_encoding($line, 'ISO-8859-1', 'UTF-8');
            $pdf->MultiCell(170, 8, $latin !== false ? $latin : $line);
        }
        $pdf->Output('F', $absolutePath);
    }

    private static function assemble(string $title, string $body): string
    {
        $stream = 'BT /F1 16 Tf 72 720 Td ('.self::escape($title).') Tj 0 -22 Td /F1 11 Tf ('.self::escape($body).') Tj ET';
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            4 => '<< /Length '.strlen($stream).' >>stream'."\n".$stream."\n".'endstream',
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $out = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($out);
            $out .= $id.' 0 obj'.$object."endobj\n";
        }

        $xref = strlen($out);
        $out .= "xref\n0 6\n0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $out .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        return $out."trailer<< /Size 6 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    private static function escape(string $value): string
    {
        $latin = mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $latin !== false ? $latin : $value);
    }
}
