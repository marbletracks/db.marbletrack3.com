<?php
namespace Database;

class ResultSetObjectResult extends ResultSetObject {
    /* PHP result resource */

    /** @var \mysqli_result|bool */
    private $result;

    /* Create a resultset */

    /**
     * @param \mysqli_result|bool $result
     */
    public function __construct($result) {
        $this->result = $result;
    }

    /* OVERIDE WITH A FASTER VERSION FOR NON-PREPARED STAMENTS Advance to the next row */

    public function next(): void {
        $ret = $this->result->fetch_assoc();
        if ($ret) {
            $this->currentRowNum++;
            $this->data = $ret;
        } else {
            $this->data = false;
        }
    }

    /* returns the number of rows in this ResultSet */

    /**
     * @return int|string|null
     */
    public function numRows() {
        if (is_object($this->result)) {
            return $this->result->num_rows;
        }
        return null;
    }

    /** @param int $rowNum */
    public function setRow($rowNum): void {
        if (is_object($this->result) && $this->numRows() > $rowNum) {
            $this->result->data_seek($rowNum);
            $this->currentRowNum = $rowNum;

            $this->data = $this->result->fetch_assoc();
        } else {
            $this->data = false;
        }
    }

    /**
     * @param mixed $var unused
     * @return array<int, object>|false
     */
    public function fieldList($var) {
        return $this->result->fetch_fields();
    }

    public function close(): void {
        if (is_object($this->result)) {
            $this->result->close();
        }
    }

    function __destruct() {
        $this->close();
    }

}
