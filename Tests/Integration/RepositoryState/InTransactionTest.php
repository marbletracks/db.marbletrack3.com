<?php

use PHPUnit\Framework\TestCase;

class InTransactionTest extends TestCase
{
    public function testTracksBeginCommitAndRollBack(): void
    {
        $db = getTestDatabase();
        $this->assertFalse($db->inTransaction());
        $db->beginTransaction();
        $this->assertTrue($db->inTransaction());
        $db->commit();
        $this->assertFalse($db->inTransaction());
        $db->beginTransaction();
        $db->rollBack();
        $this->assertFalse($db->inTransaction());
    }
}
