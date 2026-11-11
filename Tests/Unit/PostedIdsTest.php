<?php

use PHPUnit\Framework\TestCase;

class PostedIdsTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_POST['loaded_photo_ids']);
    }

    public function testReadsPositiveIntegersInOrder(): void
    {
        $_POST['loaded_photo_ids'] = ['12', '7', 'x', '-3', '0', '7'];
        $this->assertSame([12, 7, 7], \Utilities::postedIds('loaded_photo_ids'));
    }

    public function testMissingOrScalarFieldIsEmpty(): void
    {
        $this->assertSame([], \Utilities::postedIds('loaded_photo_ids'));
        $_POST['loaded_photo_ids'] = '12';
        $this->assertSame([], \Utilities::postedIds('loaded_photo_ids'));
    }
}
