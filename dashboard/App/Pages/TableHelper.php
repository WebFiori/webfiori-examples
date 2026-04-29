<?php
namespace App\Pages;

use WebFiori\Ui\HTMLTable;

/**
 * Helper to create an HTMLTable with a proper header row.
 */
class TableHelper {
    /**
     * Creates an HTMLTable with th header row and td data rows.
     *
     * @param string[] $headers Column headers.
     * @param array[]  $rows    Array of row data (each row is an array of cell values).
     */
    public static function create(array $headers, array $rows): HTMLTable {
        $table = new HTMLTable(count($rows) + 1, count($headers));

        for ($c = 0; $c < count($headers); $c++) {
            $table->getCell(0, $c)->setNodeName('th');
            $table->setValue(0, $c, $headers[$c]);
        }

        for ($r = 0; $r < count($rows); $r++) {
            for ($c = 0; $c < count($rows[$r]); $c++) {
                $table->setValue($r + 1, $c, $rows[$r][$c]);
            }
        }

        return $table;
    }
}
