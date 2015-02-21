<?php
class TransactionEntityTest extends PHPUnit_Framework_TestCase
{
    /** @var SFM_Manager */
    protected $sfm;

    public function setUp()
    {
        $this->sfm = SFM_Manager::getInstance()->reset();
        $this->connect();
        $this->runFixture();
    }

    protected function connect()
    {
        $cacheConfig = new \SFM\Cache\Config();
        $cacheConfig->setHost("localhost")
            ->setIsDisabled(false)
            ->setPort(11211)
            ->setPrefix("test")
            ->setDriver(\SFM\Cache\Adapter::MEMCACHED);

        $this->sfm->getCache()->init($cacheConfig)->connect();

        $dbConfig = new \SFM\Database\Config();
        $dbConfig->setDriver("Pdo_Mysql")
            ->setHost("localhost")
            ->setUser("sfm")
            ->setPass("sfm_pass")
            ->setDb("test");

        $this->sfm->getDb()->init($dbConfig)->connect();
    }

    protected function runFixture()
    {
        $this->sfm->getDb()->query("
        DROP TABLE IF EXISTS `mock`;
        ");

        $this->sfm->getDb()->query("
        CREATE TABLE `mock` (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `text` VARCHAR(50) NOT NULL,
            PRIMARY KEY (`id`)
        );
        ");

        $this->sfm->getDb()->query("
        INSERT INTO `mock` (`id`, `text`) VALUES (1, 'test');
        ");
    }

    public function testCommitEntity()
    {
        $engines = $this->sfm->getTransaction()->getTransactionEngines();
        $engines[] = $this->sfm->getTransaction();

        $this->sfm->getIdentityMap()->disable();
        $this->sfm->getCache()->flush();
        $this->sfm->getTransaction()->beginTransaction();

        /** @var SFM_Transaction_Engine $engine */
        foreach ($engines as $engine) {
            $this->assertEquals(true, $engine->isTransaction(), get_class($engine) . " is not started");
        }

        $entity = $this->sfm->getRepository()->get('Mapper_Mock')->getEntityById(1);
        $this->assertEquals("test", $entity->getText(), "Text before transaction update is not valid");

        $entity->update(array("text" => "test2"));
        $this->assertEquals("test2", $entity->getText(), "Text after transaction update is not valid");

        $entity = $this->sfm->getRepository()->get('Mapper_Mock')->getEntityById(1);
        $this->assertEquals("test2", $entity->getText(), "Text reloaded after transaction update is not valid");

        $this->sfm->getTransaction()->commitTransaction();

        /** @var SFM_Transaction_Engine $engine */
        foreach ($engines as $engine) {
            $this->assertEquals(false, $engine->isTransaction(), get_class($engine) . " is not finished");
        }

        $dbData = $this->sfm->getDb()->fetchLine("SELECT * FROM `mock` WHERE `id` = 1");
        $entity = $this->sfm->getRepository()->get('Mapper_Mock')->getEntityById(1);

        $this->assertEquals("test2", $dbData["text"], "Text in db is not expected");
        $this->assertEquals("test2", $entity->getText(), "Text in cache is not expected");
    }

    public function testRollbackEntity()
    {
        $engines = $this->sfm->getTransaction()->getTransactionEngines();
        $engines[] = $this->sfm->getTransaction();

        $this->sfm->getIdentityMap()->disable();
        $this->sfm->getCache()->flush();
        $this->sfm->getTransaction()->beginTransaction();

        /** @var SFM_Transaction_Engine $engine */
        foreach ($engines as $engine) {
            $this->assertEquals(true, $engine->isTransaction(), get_class($engine) . " is not started");
        }

        $entity = $this->sfm->getRepository()->get('Mapper_Mock')->getEntityById(1);
        $this->assertEquals("test", $entity->getText(), "Text before transaction update is not valid");

        $entity->update(array("text" => "test2"));
        $this->assertEquals("test2", $entity->getText(), "Text after transaction update is not valid");

        $entity = $this->sfm->getRepository()->get('Mapper_Mock')->getEntityById(1);
        $this->assertEquals("test2", $entity->getText(), "Text reloaded after transaction update is not valid");

        $this->sfm->getTransaction()->rollbackTransaction();

        /** @var SFM_Transaction_Engine $engine */
        foreach ($engines as $engine) {
            $this->assertEquals(false, $engine->isTransaction(), get_class($engine) . " is not finished");
        }

        $dbData = $this->sfm->getDb()->fetchLine("SELECT * FROM `mock` WHERE `id` = 1");
        $entity = $this->sfm->getRepository()->get('Mapper_Mock')->getEntityById(1);

        $this->assertEquals("test", $dbData["text"], "Text in db is not expected");
        $this->assertEquals("test", $entity->getText(), "Text in cache is not expected");
    }

    public function testCommitValue()
    {
        $value = new Value_Mock();
        $this->sfm->getTransaction()->beginTransaction();
        $this->sfm->getDb()->update("UPDATE `mock` SET `text` = 'test1' WHERE `id` = 1");
        $this->assertEquals("test1", $value->get(), "Value 1 fetched in transaction is not valid");
        $this->sfm->getTransaction()->commitTransaction();
        $this->assertEquals("test1", $value->get(), "Value 2 fetched in transaction is not valid");
    }

    public function testRollbackValue()
    {
        $value = new Value_Mock();
        $this->sfm->getTransaction()->beginTransaction();
        $this->sfm->getDb()->update("UPDATE `mock` SET `text` = 'test1' WHERE `id` = 1");
        $this->assertEquals("test1", $value->get(), "Value 1 fetched in transaction is not valid");
        $this->sfm->getTransaction()->rollbackTransaction();
        $this->assertEquals("test", $value->get(), "Value 2 fetched in transaction is not valid");
    }

    public function testFlushCommitValue()
    {
        $value = new Value_Mock();
        $this->sfm->getCache()->flush();
        $this->sfm->getTransaction()->beginTransaction();
        $this->sfm->getDb()->update("UPDATE `mock` SET `text` = 'test1' WHERE `id` = 1");
        $this->assertEquals("test1", $value->get(), "Value 1 fetched in transaction is not valid");
        $this->sfm->getDb()->update("UPDATE `mock` SET `text` = 'test2' WHERE `id` = 1");
        $this->assertEquals("test1", $value->get(), "Value 2 fetched in transaction is not valid");
        $value->flush();
        $this->assertEquals("test2", $value->get(), "Value 3 fetched in transaction is not valid");
        $this->sfm->getTransaction()->commitTransaction();
        $this->assertEquals("test2", $value->get(), "Value 4 fetched in transaction is not valid");
    }

    public function testFlushRollbackValue()
    {
        $value = new Value_Mock();
        $this->sfm->getCache()->flush();
        $this->sfm->getTransaction()->beginTransaction();
        $this->sfm->getDb()->update("UPDATE `mock` SET `text` = 'test1' WHERE `id` = 1");
        $this->assertEquals("test1", $value->get(), "Value 1 fetched in transaction is not valid");
        $this->sfm->getDb()->update("UPDATE `mock` SET `text` = 'test2' WHERE `id` = 1");
        $this->assertEquals("test1", $value->get(), "Value 2 fetched in transaction is not valid");
        $value->flush();
        $this->assertEquals("test2", $value->get(), "Value 3 fetched in transaction is not valid");
        $this->sfm->getTransaction()->rollbackTransaction();
        $this->assertEquals("test", $value->get(), "Value 4 fetched in transaction is not valid");
    }
}