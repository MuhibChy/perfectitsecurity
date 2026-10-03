<?php

namespace App\Services;

/**
 * Minimal native XLSX writer (Phase 2 gap-closure).
 *
 * No composer dependency: builds a valid Office Open XML workbook with
 * ZipArchive + inline strings. Correctly preserves UTF-8, commas, quotes,
 * newlines, dates (as ISO text), currency codes and decimal precision —
 * everything is stored as inline strings so Excel never reinterprets or
 * truncates values.
 */
class XlsxExportService
{
    public function build(string $title, string $period, array $summary, array $columns, array $rows): string
    {
        $esc = fn ($v): string => htmlspecialchars((string) $v, ENT_XML1 | ENT_COMPAT, 'UTF-8');

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        $r = 1;
        $addRow = function (array $cells) use (&$sheet, &$r, $esc): void {
            $sheet .= '<row r="'.$r.'">';
            $col = 0;
            foreach ($cells as $c) {
                $cell = $this->colName($col).$r;
                $sheet .= '<c r="'.$cell.'" t="inlineStr"><is><t xml:space="preserve">'.$esc($c).'</t></is></c>';
                $col++;
            }
            $sheet .= '</row>';
            $r++;
        };

        $addRow([$title]);
        $addRow(['Generated', now()->format('Y-m-d H:i'), 'Period', $period]);
        $addRow(['PerfectITSecurity — Confidential']);
        foreach ($summary as $k => $v) {
            $addRow([$k, $v]);
        }
        $addRow([]);
        $addRow($columns);
        foreach ($rows as $row) {
            $addRow(array_map(fn ($v) => $v === null ? '' : (string) $v, $row));
        }
        $sheet .= '</sheetData></worksheet>';

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx').'.xlsx';
        $zip = new \ZipArchive();
        if ($zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create spreadsheet archive.');
        }
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Report" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'</Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        $zip->close();

        return $tmp;
    }

    protected function colName(int $i): string
    {
        $name = '';
        $i++;
        while ($i > 0) {
            $mod = ($i - 1) % 26;
            $name = chr(65 + $mod).$name;
            $i = intdiv($i - 1, 26);
        }

        return $name;
    }
}
