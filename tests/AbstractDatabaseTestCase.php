<?php
namespace Szurubooru\Tests;

abstract class AbstractDatabaseTestCase extends \Szurubooru\Tests\AbstractTestCase
{
	protected $databaseConnection;

	public function setUp()
	{
		parent::setUp();
		$config = $this->mockConfig($this->createTestDirectory());
		$config->set('database/dsn', 'sqlite::memory:');

		$this->databaseConnection = new \Szurubooru\DatabaseConnection($config);

		$upgradeRepository = \Szurubooru\Injector::get(\Szurubooru\Upgrades\UpgradeRepository::class);
		$upgradeService = new \Szurubooru\Services\UpgradeService($config, $this->databaseConnection, $upgradeRepository);
		$upgradeService->runUpgradesQuiet();
	}

	public function tearDown()
	{
		parent::tearDown();
		if ($this->databaseConnection)
			$this->databaseConnection->close();
	}

	protected function assertEntitiesEqual($expected, $actual)
	{
		if (!is_array($expected))
		{
			$expected = [$expected];
			$actual = [$actual];
		}
		$this->assertEquals(count($expected), count($actual), 'Unmatching array sizes');
		$this->assertEquals(array_keys($expected), array_keys($actual), 'Unmatching array keys');
		foreach (array_keys($expected) as $key)
		{
			if ($expected[$key] === null)
			{
				$this->assertNull($actual[$key]);
			}
			else
			{
				$expected[$key]->resetLazyLoaders();
				$expected[$key]->resetMeta();
				$actual[$key]->resetLazyLoaders();
				$actual[$key]->resetMeta();
				$this->assertEquals($expected[$key], $actual[$key]);
			}
		}
	}
}
