<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\ORM\Tests;


use EasySwoole\ORM\Db\Config;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Tests\models\TestRelationModel;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    /**
     * @var $connection Connection
     */
    protected $connection;

    protected $ids = [];

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $config = new Config(MYSQL_CONFIG);
        $config->setReturnCollection(true);
        $this->connection = new Connection($config);
        DbManager::getInstance()->addConnection($this->connection);
        $connection = DbManager::getInstance()->getConnection();
        $this->assertTrue($connection === $this->connection);
    }

    public function testAdd()
    {
        $model = new TestRelationModel();
        $time = date('Y-m-d');
        $this->ids[] = $model->data([
            'name' => 'gaobinzhan',
            'age' => 20,
            'addTime' => $time,
            'state' => 1
        ])->save();
        $this->ids[] = TestRelationModel::create()->data([
            'name' => 'gaobinzhan',
            'age' => 20,
            'addTime' => $time,
            'state' => 1
        ])->save();
        $this->assertEquals("INSERT  INTO `test_user_model` (`name`, `age`, `addTime`, `state`)  VALUES ('gaobinzhan', 20, '{$time}', 1)", $model->lastQuery()->getLastQuery());
    }

    public function testSelect()
    {
        $model = new TestRelationModel();
        $this->testAdd();
        $model->where('id', $this->ids, 'in')->all()->toArray();
        $ids = implode(', ', $this->ids);
        $this->assertEquals("SELECT  * FROM `test_user_model` WHERE  `id` in ( {$ids} ) ", $model->lastQuery()->getLastQuery());
    }

    public function testUpdate()
    {
        $model = new TestRelationModel();
        $this->testAdd();
        foreach ($this->ids as $id) {
            $model->where('id', $id)->update(['name' => 'gaobinzhan1']);
            $this->assertEquals("UPDATE `test_user_model` SET `name` = 'gaobinzhan1' WHERE  `id` = {$id} ", $model->lastQuery()->getLastQuery());
        }
        $id = current($this->ids);
        $model->where('id', $id)->update(['age' => '22']);
        $this->assertEquals("UPDATE `test_user_model` SET `age` = '22' WHERE  `id` = {$id} ", $model->lastQuery()->getLastQuery());

    }

    public function testDelete()
    {
        $model = new TestRelationModel();
        $model->destroy(null, true);
        $this->testAdd();
        foreach ($this->ids as $id) {
            $model->where('id', $id)->destroy();
            $this->assertEquals("DELETE FROM `test_user_model` WHERE  `id` = {$id} ", $model->lastQuery()->getLastQuery());
        }
    }
}