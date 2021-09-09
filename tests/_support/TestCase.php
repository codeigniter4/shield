<?php namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;

class TestCase extends CIUnitTestCase
{
	protected function setUp(): void
	{
		$this->resetServices();

		parent::setUp();
	}
}
