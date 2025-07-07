<?php

namespace Physical;

/**
 * Column is a part of a page, like a section in a notebook.
 * It has a name, sort order, and is associated with a worker.
 * For columns on earlier pages, the workers are listed per line,
 * so the columns will be more like tines on a comb than columns.
 */
class Column
{
    /**
     * Column constructor.
     *
     * @param int $column_id The unique identifier for the column.
     * @param int $page_id The ID of the page this column belongs to.
     * @param int $worker_id The ID of the worker associated with this column.
     * @param string $col_name The name of the column.
     * @param int $col_sort The sort order of the column.
     * @param string $created_at The creation timestamp of the column.
     */
    public function __construct(
        public int $column_id,
        public int $page_id,
        public int $worker_id,
        public string $col_name,
        public int $col_sort,
        public string $created_at
    ) {
    }
}
