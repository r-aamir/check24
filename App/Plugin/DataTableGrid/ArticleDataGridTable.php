<?php

declare(strict_types=1);

namespace App\Plugin\DataTableGrid;

class ArticleDataGridTable extends DataGridTable
{
    private static $def = [
        'names' => [1 => 'id', 'author_id', 'article_date', 'content', 'updated_at'],
        'order' => [1 => 'a', 'a', 'd', 'a', 'd'],
        'label' => [1 => 'ID ', 'Author', 'Date', 'Content', 'Last Update'],
        'width' => [1 => 90, null, null, null, null],
    ];

    /**
     * {@inheritDoc}
     */
    protected static function getDefaultSortColumn() : int
    {
        return 1;
    }

    /**
     * {@inheritDoc}
     */
    protected static function getDefinition() : array
    {
        return self::$def;
    }
}
