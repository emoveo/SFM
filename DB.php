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
    * Creates a new DB connection object and connect to the database
    *
    * @param string $name Name of the connection
    * @throws SFM_Exception_DB
    */
    protected function __construct($connectionName)
    {
        $config = Zend_Registry::get(Application::CONFIG_NAME);
        try {
            $config = Zend_Registry::get(Application::CONFIG_NAME);
			$this->_db = Zend_Db::factory($config->database->main->driver,$config->database->main->params);
			if(!empty($config->database->main->initialQuery))
				$this->_db->query($config->database->main->initialQuery);
            /*$dsn = $Config->database->{$connectionName}->driver.':host='.$Config->database->{$connectionName}->params->host.';dbname='.$Config->database->{$connectionName}->params->dbname;
            $this->pdo = new PDO($dsn, $Config->database->{$connectionName}->params->username, $Config->database->{$connectionName}->params->password);
            $this->pdo->exec("SET NAMES 'utf8'");*/
            
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
    	$stmt = $this->query($sql, $vars);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $stmt = $this->query($sql, $vars);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
        $stmt = $this->query($sql, $vars);
        return $stmt->fetchColumn();
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
        $result = array();
        $stmt = $this->query($sql, $params);
        while ( $id = $stmt->fetchColumn() ) {
            $result[] = $id;
        }
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
    	$stmt = $this->query($sql, $vars);
        return $stmt->rowCount();
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
    
    public function insert($sql, $vars, $tableName = null, $idFieldName = 'id')
    {
        $stmt = $this->query($sql, $vars);
       	return $this->_db->lastInsertId($tableName,$idFieldName);
    }
    
    public function delete($sql, $vars)
    {
        $stmt = $this->query($sql, $vars);
        return $stmt->rowCount();
    }
    
    /**
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->_db->beginTransaction();
    }
    
    /**
     * @return bool
     */
    public function commit()
    {
        return $this->_db->commit();
    }
    /**
     * @return bool
     */
    public function rollBack()
    {
        return $this->_db->rollBack();
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
