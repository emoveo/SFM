<?php

class SFM_DB implements SFM_Transaction_Engine, SFM_MonitorableInterface
{
    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $db = null;
    
    /**
     * Current transaction level
     * @var integer
     */
    protected $transactionLevel = 0;

    /**
     * @var SFM_Config_Database
     */
    protected $config = null;

    /**
     * @var SFM_MonitorInterface
     */
    protected $monitor;

    /**
     * @param SFM_MonitorInterface $monitor
     */
    public function setMonitor(SFM_MonitorInterface $monitor)
    {
        $this->monitor = $monitor;
    }

    /**
     * Creates a new DB connection object and connect to the database
     * @throws SFM_Exception_DB
     */
    public function connect()
    {
        if (is_null($this->config)) {
            throw new SFM_Exception_DB("SFM_DB is not configured");
        }

        try {
            $this->db = new \Zend\Db\Adapter\Adapter(array(
                'driver' => $this->config->getDriver(),
                'database' => $this->config->getDb(),
                'username' => $this->config->getUser(),
                'password' => $this->config->getPass(),
                'hostname' => $this->config->getHost()
            ));

            if (is_array($this->config->getInitialQueries())) {
                foreach ($this->config->getInitialQueries() as $query) {
                    $this->db->query($query,array());
                }
            }
            
        } catch (Zend\Db\Adapter\Exception\ExceptionInterface $e) {
            throw new SFM_Exception_DB('Error while connecting to db', array('error' => $e->getMessage()));
        }

    }

    /**
     * @param SFM_Config_Database $config
     * @return $this
     */
    public function init(SFM_Config_Database $config)
    {
        $this->config = $config;

        return $this;
    }
    
    /**
     *  @return \Zend\Db\Adapter\Adapter
     */
    public function getAdapter()
    {
        return $this->db;
    }

    /**
     * @return string
     */
    public function getQuoteSymbol()
    {
        return $this->db->getPlatform()->getQuoteIdentifierSymbol();
    }

    /**
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier)
    {
        return $this->db->getPlatform()->quoteIdentifier($identifier);
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
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'fetchAll'));
        }

        /** @var \Zend\Db\ResultSet\ResultSet $stmt */
        $stmt = $this->query($sql, $vars);

        $data = array();
        /** @var ArrayObject $row */
        foreach ($stmt as $row) {
            $data[] = (array) $row;
        }

        if (isset($timer)) {
            $timer->stop();
        }

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
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'fetchLine'));
        }

        $stmt = $this->query($sql, $vars);

        if (isset($timer)) {
            $timer->stop();
        }

        $result = $stmt->current();

        return $result ? (array) $result : null;
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
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'fetchValue'));
        }

        $stmt = $this->query($sql, $vars);
        $array = (array) $stmt->current();
        $data = array_shift($array);

        if (isset($timer)) {
            $timer->stop();
        }
        return $data;
    }

    /**
     * Sends update query to DB. Actually, it is a wrapper and now it's empty. I reserved it for future purposes
     *
     * @param string $sql
     * @param array $vars
     * @return int Number of rows affected bt update
     */
    public function update($sql, $vars = array())
    {
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'update'));
        }

        $stmt = $this->query($sql, $vars);
        $data = $stmt->count();

        if (isset($timer)) {
            $timer->stop();
        }

        return $data;
    }


    /**
     * Prepares, binds params and executes query
     *
     * @param string $sql SQL query with placeholders
     * @param array $vars Array of variables
     * @throws SFM_Exception_DB
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function query($sql, $vars = array())
    {
        try {
            $result = $this->db->query($sql, $vars);
        } catch (Zend\Db\Adapter\Exception\ExceptionInterface $e) {

            $context = array(
                'message' => $e->getMessage(),
                'sql'     => $sql,
                'vars'    => $vars
            );
            throw new SFM_Exception_DB("Query error", $context, $e);
        }

        return $result;
    }

    /**
     * @param $sql
     * @param $vars
     * @param string $idFieldName
     * @param bool $isIdAutoincrement
     * @return string
     */
    public function insert($sql, $vars, $idFieldName = 'id', $isIdAutoincrement = true)
    {
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'insert'));
        }

        $this->query($sql, $vars);

        if (isset($timer)) {
            $timer->stop();
        }

        if($isIdAutoincrement){
            return $this->db->getDriver()->getLastGeneratedValue();
        } else {
            return $vars[$idFieldName];
        }
    }
    
    public function delete($sql, $vars)
    {
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'delete'));
        }

        $stmt = $this->query($sql, $vars);
        $data = $stmt->count();

        if (isset($timer)) {
            $timer->stop();
        }

        return $data;
    }

    /**
     * @return bool
     * @throws SFM_Exception_DB
     */
    public function beginTransaction()
    {
        $result = true;
        if ($this->transactionLevel == 0) {

            if ($this->monitor !== null) {
                $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'beginTransaction'));
            }
            $this->transactionLevel++;

            try {
                $this->db->getDriver()->getConnection()->beginTransaction();
            } catch (Zend\Db\Adapter\Exception\ExceptionInterface $e) {

                $context = array(
                    'message' => $e->getMessage()
                );
                throw new SFM_Exception_DB("Transaction begin error", $context, $e);
            }

            if (isset($timer)) {
                $timer->stop();
            }
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isTransaction()
    {
        return $this->transactionLevel > 0;
    }
    
    /**
     * @return bool
     * @throws SFM_Exception_DB
     */
    public function commitTransaction()
    {
        if ($this->transactionLevel < 0) {
            throw new SFM_Exception_DB('Commit without begin occured');
        }

        $result = true;
        $this->transactionLevel--;
        if ($this->transactionLevel == 0) {

            if ($this->monitor !== null) {
                $timer = $this->monitor->createTimer(array('db' => 'sql', 'operation' => 'commitTransaction'));
            }

            try {
                $this->db->getDriver()->getConnection()->commit();
            } catch (Zend\Db\Adapter\Exception\ExceptionInterface $e) {

                $context = array(
                    'message' => $e->getMessage()
                );
                throw new SFM_Exception_DB("Transaction commit error", $context, $e);
            }

            if (isset($timer)) {
                $timer->stop();
            }

        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @return bool
     * @throws SFM_Exception_DB
     */
    public function rollbackTransaction()
    {
        //only if any transaction is started and was not rollbacked
        if($this->transactionLevel != 0) {
            $this->transactionLevel = 0;

            if ($this->monitor !== null) {
                $timer = $this->monitor->createTimer(array('db' => 'sql', 'operation' => 'rollbackTransaction'));
            }

            try {
                $this->db->getDriver()->getConnection()->rollBack();
            } catch (Zend\Db\Adapter\Exception\ExceptionInterface $e) {

                $context = array(
                    'message' => $e->getMessage()
                );
                throw new SFM_Exception_DB("Transaction rollback error", $context, $e);
            }

            if (isset($timer)) {
                $timer->stop();
            }
            return true;
        } else {
            return false;
        }
    }

    public function setProfiler($profiler)
    {
        $this->db->setProfiler($profiler);
    }
}
