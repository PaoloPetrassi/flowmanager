<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class DocumentGenerationService
{
    public function renderTemplate(string $content, Model $model): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function (array $matches) use ($model) {
            $value = data_get($model, $matches[1]);

            if ($value instanceof \BackedEnum) {
                return (string) $value->value;
            }

            if ($value instanceof \DateTimeInterface) {
                return $value->format('d/m/Y');
            }

            return is_scalar($value) ? (string) $value : '';
        }, $content) ?? $content;
    }

    public function word(string $title, string $body): string
    {
        $safeTitle = e($title);
        $safeBody = nl2br(e($body));

        return <<<HTML
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
        <head><meta charset="utf-8"><title>{$safeTitle}</title></head>
        <body style="font-family:Arial,sans-serif;font-size:11pt"><h1>{$safeTitle}</h1><div>{$safeBody}</div></body>
        </html>
        HTML;
    }

    public function pdf(string $title, string $body): string
    {
        $lines = collect(preg_split('/\R/u', $body) ?: [])
            ->flatMap(fn (string $line) => $this->wrapLine($line, 92))
            ->values();

        $pages = $lines->chunk(42);
        if ($pages->isEmpty()) {
            $pages = collect([collect()]);
        }

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        ];
        $pageRefs = [];
        $nextId = 4;

        foreach ($pages as $pageIndex => $pageLines) {
            $pageId = $nextId++;
            $contentId = $nextId++;
            $pageRefs[] = $pageId.' 0 R';
            $commands = [$this->text(14, 48, 800, $title)];
            $y = 772;

            foreach ($pageLines as $line) {
                $commands[] = $this->text(10, 48, $y, $line);
                $y -= 17;
            }

            $commands[] = $this->text(8, 500, 28, ($pageIndex + 1).' / '.$pages->count());
            $stream = implode("\n", $commands);
            $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents {$contentId} 0 R >>";
            $objects[$contentId] = '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream";
        }

        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $pageRefs).'] /Count '.count($pageRefs).' >>';
        ksort($objects);

        return $this->assemble($objects);
    }

    private function wrapLine(string $line, int $width): array
    {
        if ($line === '') {
            return [''];
        }

        return explode("\n", wordwrap($line, $width, "\n", true));
    }

    private function text(float $size, float $x, float $y, string $value): string
    {
        $encoded = function_exists('iconv') ? iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value) : $value;
        $encoded = $encoded === false ? $value : $encoded;
        $escaped = str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', ' ', ' '], $encoded);

        return sprintf('BT /F1 %.1F Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET', $size, $x, $y, $escaped);
    }

    private function assemble(array $objects): string
    {
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0 => 0];

        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id." 0 obj\n".$object."\nendobj\n";
        }

        $xref = strlen($pdf);
        $max = max(array_keys($objects));
        $pdf .= "xref\n0 ".($max + 1)."\n0000000000 65535 f \n";
        for ($id = 1; $id <= $max; $id++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$id] ?? 0)."\n";
        }
        $pdf .= "trailer\n<< /Size ".($max + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }
}
