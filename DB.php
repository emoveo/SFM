<?php
require_once 'Zend/Registry.php';
require_once 'SFM/Interface/Singleton.php';
require_once 'SFM/Exception/DB.php';
/**
 * Database abstract layer class. Based on PDO
 *
 */
class SFM_DB implements SFM_Interface_Singleton
{
    /**
    * DB object
    * @var array
    */
    private static $instances = array();

    /**
     * PDO object
     * @var PDO
     */
    private $pdo = null;
    
    /**
     * 
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db = null;
    
    /**
     * Current transaction level
     * @var integer
     */
    protected $_transactionLevel = 0;

    /**
     * Creates a new DB connection object and connect to the database
     * @param string $connectionName Name of the connection
     * @throws SFM_Exception_DB
     */
    protected function __construct($connectionName)
    {
        try {
            $config = Zend_Registry::get(Application::CONFIG_NAME);
            $connectionConfig = $config->database->{$connectionName};

            if (is_null($connectionConfig)) {
                throw new SFM_Exception_DB("Connection `{$connectionName}` is not exist");
            }

            $this->_db = Zend_Db::factory($connectionConfig->driver, $connectionConfig->params);
            if (!empty($connectionConfig->initialQuery)) {
                $this->_db->query($connectionConfig->initialQuery);
            }
            
        } catch (Zend_Db_Exception $e) {
            throw new SFM_Exception_DB('Error while connecting to db. '.$e->getMessage());
        }

    }

    /**
     * Returns PDO connection
     *
     * @param string $connectionName Name of the connection
     * @return SFM_DB
     */
    public static function getInstance($connectionName=null)
    {
        if ($connectionName === null) {
            $config = Zend_Registry::get(Application::CONFIG_NAME);
            $connectionName = $config->database->default;
        }
        if (!isset(self::$instances[$connectionName])) {
            self::$instances[$connectionName] = new SFM_DB($connectionName);
        }

        return self::$instances[$connectionName];
    }
    
    /**
     * Returns adapter object
     *	@return Zend_Db_Adapter_Abstract 
     */
    public function getAdapter()
    {
    	return $this->_db;
    	
    }
    
    public function getQuoteSymbol()
    {
    	return $this->getAdapter()->getQuoteIdentifierSymbol();
    }

    public function quoteIdentifier($ident, $auto=false)
    {
    	return $this->getAdapter()->quoteIdentifier($ident,$auto);
    }
    
    
    /**
     * Returns all lines from query
     *
     * @param string $sql
     * @param array $vars
     * @return array
     */
    public function fetchAll($sql, array $vars=array())
    {
        $timer = SFM_Monitor::get()->createTimer(array('db' => 'sql', 'operation' => 'fetchAll'));
    	$stmt = $this->query($sql, $vars);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $timer->stop();
        return $data;
    }

    /**
     * Returns line from the query result
     *
     * @param string $sql
     * @param array $vars
     * @return Array
     */
    public function fetchLine($sql, array $vars=array())
    {
        $timer = SFM_Monitor::get()->createTimer(array('db' => 'sql', 'operation' => 'fetchLine'));
        $stmt = $this->query($sql, $vars);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $timer->stop();
        return $data;
    }

    /**
     * Returns value from the query result
     *
     * @param string $sql
     * @param array $vars
     * @return string
     */
    public function fetchValue($sql, array $vars=array())
    {
        $timer = SFM_Monitor::get()->createTimer(array('db' => 'sql', 'operation' => 'fetchValue'));
        $stmt = $this->query($sql, $vars);
        $data = $stmt->fetchColumn();
        $timer->stop();
        return $data;
    }
    
    /**
     * Return all data from first column
     * Most typical usecase get all ids of aggregate
     *
     * @param string $sql
     * @param array $params
     * @return array contains column values
     */
    public function fetchColumn($sql, array $params)
    {
        $timer = SFM_Monitor::get()->createTimer(array('db' => 'sql', 'operation' => 'fetchColumn'));
        $result = array();
        $stmt = $this->query($sql, $params);
        while ( $id = $stmt->fetchColumn() ) {
            $result[] = $id;
        }
        $timer->stop();
        return $result; 
    }

    /**
     * Sends update query to DB. Actually, it is a wrapper and now it's empty. I reserved it for future purposes
     *
     * @param string $sql
     * @param array $vars
     * @return int Number of rows affected bt update
     */
    public function update($sql, $vars)
    {
        $timer = SFM_Monitor::get()->createTimer(array('db' => 'sql', 'operation' => 'update'));
    	$stmt = $this->query($sql, $vars);
        $data = $stmt->rowCount();
        $timer->stop();
        return $data;
    }


    /**
     * Prepares, binds params and executes query
     *
     * @param string $sql SQL query with placeholders
     * @param array $vars Array of variables
     * @return PDOStatement
     */
    private function query($sql, $vars)
    {
        //echo "\n {$sql} ".var_export($vars, true);
        /*$stmt = $this->pdo->prepare($sql);
        if( false === $stmt ) {
            //PDO throw excetions if only database connection problems
            throw new SFM_Exception_DB('PDO prepair error with sql - '.$sql); 
        }
        foreach ($vars as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        if (!$stmt->execute()) {
            throw new SFM_Exception_DB('Error occured while running sql: ' . var_export($stmt->errorInfo(), true));
        }
        return $stmt;*/
    	//Reflection::export(new ReflectionObject(/*'getQuoteIdentifierSymbol',*/$this->_db));
    	//var_dump($this->_db->getQuoteIdentifierSymbol());
    	return $this->_db->query($sql, $vars);
    }
    
    /**
   	 * @param string $sql
   	 * @param array $vars
   	 * @param string|null $tableName it is necessary for postgres to generate last sequence id
     *
     */
    
    public function insert($sql, $vars, $tableName = null, $idFieldName = 'id',$isIdAutoincrement = true)
    {
        $timer = SFM_Monitor::get()->createTimer(array('db' => 'sql', 'operation' => 'insert'));
        $stmt = $this->query($sql, $vars);
        $timer->stop();
        if($isIdAutoincrement){
            return $this->_db->lastInsertId($tableName,$idFieldName);    
        } else {
            return $vars[$idFieldName];
        }
    }
    
    public function delete($sql, $vars)
    {
        $timer = SFM_Monitor::get()->createTimer(array('db' => 'sql', 'operation' => 'delete'));
        $stmt = $this->query($sql, $vars);
        $data = $stmt->rowCount();
        $timer->stop();

        return $data;
    }
    
    /**
     * Starts a transaction. Returns if the transaction was started or no (if the transaction is nested).
     * @return integer
     */
    public function beginTransaction()
    {
        if($this->_transactionLevel == 0) {
            $timer = SFM_Monitor::get()->createTimer(array('db' => 'sql', 'operation' => 'beginTransaction'));
            $this->_transactionLevel++;
            $this->_db->beginTransaction();
            $timer->stop();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * @return bool
     */
    public function commit()
    {
        if($this->_transactionLevel < 0)
            throw new SFM_Exception_DB('Commit without begin occured');
        $this->_transactionLevel--;
        if($this->_transactionLevel == 0) {
            $timer = SFM_Monitor::get()->createTimer(array('db' => 'sql', 'operation' => 'commitTransaction'));
            $this->_db->commit();
            $timer->stop();
            return true;
        } else {
            return false;
        }
    }
    /**
     * Rollback stops all transactions, including nested ones
     * @return bool
     */
    public function rollBack()
    {
        //only if any transaction is started and was not rollbacked
        if($this->_transactionLevel != 0) {
            $this->_transactionLevel = 0;
            $timer = SFM_Monitor::get()->createTimer(array('db' => 'sql', 'operation' => 'rollbackTransaction'));
            $this->_db->rollBack();
            $timer->stop();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     *	@return void 
     *
     */
    public function setProfiler($profiler)
    {
    	$this->_db->setProfiler($profiler);	
    }
}
