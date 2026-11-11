<?php
namespace Database;

class ResultSetObjectStmt extends ResultSetObject {
    /* PHP result resource */

    /** @var \mysqli_stmt */
    private $stmt;
    /** @var array<int, object> */
    private $fields;
    /** @var array<string, mixed> */
    public $data = array();     // optimally public readonly.   Will hold the results of each statement for us to access

    /* Create a resultset */

    /**
     * @param \mysqli_stmt $stmt
     */
    public function __construct($stmt) {
        $this->stmt = $stmt;
        $this->fields = $this->loadFieldInfo($this->stmt);
    }

    /* returns the number of rows in this ResultSet */

    /**
     * @return int|string|null
     */
    public function numRows() {
        return $this->stmt->num_rows;
    }

    /** @param int $rowNum */
    public function setRow($rowNum): void {
        if ($this->numRows() > $rowNum) {
            $this->stmt->data_seek($rowNum);
            $this->currentRowNum = $rowNum;
            $vars = [];

            foreach ($this->fields as $field) {
                $fieldname = $field->name;
                $vars[$fieldname] = null;
                $this->data[$fieldname] = & $vars[$fieldname];
            }

            // PHP 8.1 requires spread operator https://www.phind.com/search?cache=nv6roe91bx0vkkqbt7x0w3uk
            // @phpstan-ignore argument.byRef (the values are references to $vars, made just above)
            if (!$this->stmt->bind_result(...array_values($this->data))) {
                throw new \Database\EDatabaseException("Bind result failed.  Check for duplicate vars in SELECT");
            }

            $this->stmt->fetch();
        } else {
            $this->data = array();     // must be an array for line 35 above
        }
    }

    /**
     * @param mixed $var unused
     * @return array<int, object>|false
     */
    public function fieldList($var) {
        return $this->fields;
    }

    public function close(): void {
        $this->stmt->free_result();
        $this->stmt->close();
    }

    /**
     * @param \mysqli_stmt $resultSet
     * @return array<int, object>
     */
    private function loadFieldInfo($resultSet) {
        $metadata = $resultSet->result_metadata();
        $fields = $metadata->fetch_fields();
        $metadata->close();
        return $fields;
    }

    function __destruct() {
        $this->close();
    }

}
