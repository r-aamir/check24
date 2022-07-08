<?php

declare(strict_types=1);

namespace App\Plugin\DataTableGrid;

/**
 * Description of DataGridTable
 */
abstract class DataGridTable
{
    /**
     * Converts the request parameter value to SQL ORDER BY
     *
     * @param mixed  $definition The referenced output definition.
     * @param string $request    The requested sort order.
     * @return array The SQL ORDER BY.
     * @throws LogicException
     */
    public static function sortOrder(&$definition, string $request) : array
    {
        $definition = static::getDefinition();

        if (strlen($request) === 2 && $request[1] > 0 && $request[1] <= count($definition['order'])) {
            $sortColumn = (int) $request[1];
            $direction  = $request[0];
        } else {
            $sortColumn = static::getDefaultSortColumn();

            if (! isset($definition['order'][$sortColumn])) {
                throw new LogicException();
            }

            $direction = $definition['order'][$sortColumn];
        }

        if (strtolower($direction) === 'a') {
            $definition['order'][$sortColumn] = 'd';
            $sqlDirection                     = 'ASC';
        } else {
            $definition['order'][$sortColumn] = 'a';
            $sqlDirection                     = 'DESC';
        }

        return [$sortColumn, $sqlDirection];
    }

    /**
     * Used for sorting data as the default column.
     * <br>
     * <br>
     * <b>Note:</b> Unlike an index, this number starts at 1.
     */
    abstract protected static function getDefaultSortColumn() : int;

    /**
     * Configuration definition of each data-grid column as an associative array.
     * The array has 3 root elements:
     *  - order: Array with values either `a` or `d` represents `ASC` or `DESC` respectively.
     *  - label: Array of titles for each data-grid column.
     *  - width: The UI column width of each data-field or NULL.
     * <br>
     * <code>[
     *  'order' => [1 => 'a', 'a', 'd'],
     *  'label' => [1 => 'ID ', 'NAME', 'VISITORS'],
     *  'width' => [1 => 90, null, null]
     * ]</code>
     * 
     * In example above we got 4 data-grid columns, by default all columnns
     *  having ASC sort order, except the last column VISITORS, where descending
     *  is the default sort order 
     */
    abstract protected static function getDefinition() : array;
}
