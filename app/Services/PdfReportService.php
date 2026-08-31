<?php

namespace App\Services;

use Illuminate\Support\Collection;

class PdfReportService
{
    private const PAGE_WIDTH = 842;
    private const PAGE_HEIGHT = 595;
    private const MARGIN_X = 34;
    private const HEADER_Y = 555;
    private const TABLE_TOP_Y = 520;
    private const ROW_HEIGHT = 18;
    private const ROWS_PER_PAGE = 25;

    public function render(
        string $title,
        array $columns,
        Collection $rows,
        ?string $subtitle = null
    ): string {
        $pages = $rows->chunk(self::ROWS_PER_PAGE);

        if ($pages->isEmpty()) {
            $pages = collect([collect()]);
        }

        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';

        $pageObjectIds = [];
        $nextObjectId = 4;
        $pageNumber = 1;

        foreach ($pages as $pageRows) {
            $pageId = $nextObjectId++;
            $contentId = $nextObjectId++;
            $pageObjectIds[] = $pageId.' 0 R';

            $content = $this->pageContent(
                $title,
                $subtitle,
                $columns,
                $pageRows,
                $pageNumber,
                $pages->count()
            );

            $objects[$pageId] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] /Resources << /Font << /F1 3 0 R >> >> /Contents %d 0 R >>',
                self::PAGE_WIDTH,
                self::PAGE_HEIGHT,
                $contentId
            );
            $objects[$contentId] = "<< /Length ".strlen($content)." >>\nstream\n{$content}\nendstream";
            $pageNumber++;
        }

        $objects[2] = sprintf(
            '<< /Type /Pages /Kids [%s] /Count %d >>',
            implode(' ', $pageObjectIds),
            count($pageObjectIds)
        );

        ksort($objects);

        return $this->assemble($objects);
    }

    private function pageContent(
        string $title,
        ?string $subtitle,
        array $columns,
        Collection $rows,
        int $pageNumber,
        int $pageCount
    ): string {
        $commands = [];
        $commands[] = $this->textCommand(14, self::MARGIN_X, self::HEADER_Y, $title);

        if ($subtitle) {
            $commands[] = $this->textCommand(8, self::MARGIN_X, self::HEADER_Y - 16, $subtitle);
        }

        $commands[] = $this->textCommand(
            7,
            748,
            self::HEADER_Y,
            __('Page :current of :total', [
                'current' => $pageNumber,
                'total' => $pageCount,
            ])
        );

        $columnKeys = array_keys($columns);
        $columnCount = max(1, count($columnKeys));
        $availableWidth = self::PAGE_WIDTH - (self::MARGIN_X * 2);
        $columnWidth = $availableWidth / $columnCount;
        $fontSize = $columnCount >= 8 ? 5.5 : 6.5;
        $charLimit = max(8, (int) floor($columnWidth / ($fontSize * 0.52)));

        $y = self::TABLE_TOP_Y;
        $commands[] = '0.92 g '.self::MARGIN_X.' '.($y - 4).' '.$availableWidth.' 16 re f 0 g';

        foreach (array_values($columns) as $index => $label) {
            $x = self::MARGIN_X + ($index * $columnWidth) + 3;
            $commands[] = $this->textCommand(
                6.5,
                $x,
                $y + 1,
                $this->truncate((string) $label, $charLimit)
            );
        }

        $y -= self::ROW_HEIGHT;

        foreach ($rows as $row) {
            $commands[] = '0.86 G '.self::MARGIN_X.' '.($y - 4).' m '.(self::PAGE_WIDTH - self::MARGIN_X).' '.($y - 4).' l S 0 G';

            foreach ($columnKeys as $index => $key) {
                $x = self::MARGIN_X + ($index * $columnWidth) + 3;
                $value = (string) ($row[$key] ?? '');
                $commands[] = $this->textCommand(
                    $fontSize,
                    $x,
                    $y + 1,
                    $this->truncate($value, $charLimit)
                );
            }

            $y -= self::ROW_HEIGHT;
        }

        return implode("\n", $commands);
    }

    private function textCommand(
        float $fontSize,
        float $x,
        float $y,
        string $text
    ): string {
        return sprintf(
            'BT /F1 %.1F Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET',
            $fontSize,
            $x,
            $y,
            $this->escapeText($text)
        );
    }

    private function truncate(string $value, int $limit): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);

        return mb_strlen($value) > $limit
            ? mb_substr($value, 0, max(1, $limit - 1)).'…'
            : $value;
    }

    private function escapeText(string $value): string
    {
        $encoded = function_exists('iconv')
            ? iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value)
            : $value;

        $encoded = $encoded === false ? $value : $encoded;

        return str_replace(
            ['\\', '(', ')', "\r", "\n"],
            ['\\\\', '\\(', '\\)', ' ', ' '],
            $encoded
        );
    }

    /**
     * @param array<int, string> $objects
     */
    private function assemble(array $objects): string
    {
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0 => 0];

        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id." 0 obj\n".$object."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $maxId = max(array_keys($objects));
        $pdf .= "xref\n0 ".($maxId + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($id = 1; $id <= $maxId; $id++) {
            $offset = $offsets[$id] ?? 0;
            $pdf .= sprintf('%010d 00000 n ', $offset)."\n";
        }

        $pdf .= "trailer\n<< /Size ".($maxId + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }
}
