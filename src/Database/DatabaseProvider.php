<?php
namespace SFM\Database;

use SFM\Transaction\TransactionException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Driver\Pgsql\Pgsql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Exception\ExceptionInterface;
use SFM\Transaction\TransactionEngineInterface;
use SFM\BaseException;

class DatabaseProvider implements TransactionEngineInterface
{
    /**
     * @var Adapter
     */
    protected $adapter = null;

    /**
     * Current transaction status
     * @var bool
     */
    protected $isTransactionActive = false;

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     *  @return Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return string
     */
    public function getQuoteSymbol()
    {
        return $this->adapter->getPlatform()->getQuoteIdentifierSymbol();
    }

    /**
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier)
    {
        return $this->adapter->getPlatform()->quoteIdentifier($identifier);
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
        /** @var ResultSet $stmt */
        $stmt = $this->query($sql, $vars);

        $data = array();
        /** @var \ArrayObject $row */
        foreach ($stmt as $row) {
            $data[] = (array) $row;
        }

        return $data;
    }

    /**
     * Returns line from the query result
     *
     * @param string $sql
     * @param array $vars
     * @return array
     */
    public function fetchLine($sql, array $vars=array())
    {
        $stmt = $this->query($sql, $vars);

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
        $stmt = $this->query($sql, $vars);
        $array = (array) $stmt->current();
        $data = array_shift($array);

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
        $stmt = $this->query($sql, $vars);
        $data = $stmt->count();

        return $data;
    }


    /**
     * Prepares, binds params and executes query

     * @param string $sql SQL query with placeholders
     * @param array $vars Array of variables
     * @throws BaseException
     * @return ResultSet
     */
    public function query($sql, $vars = array())
    {
        try {
            $result = $this->adapter->query($sql, $vars);
        } catch (ExceptionInterface $e) {
            throw new BaseException("Query error", 0, $e);
        }

        return $result;
    }

    /**
     * @param $sql
     * @param $vars
     * @param string $idFieldName
     * @param bool $isIdAutoincrement
     * @return int
     */
    public function insert($sql, $vars, $idFieldName = 'id', $isIdAutoincrement = true)
    {
        $this->query($sql, $vars);

        $seqName = null;
        if ($this->adapter->getDriver() instanceof Pgsql) {
            $seqName = $this->getPgSeqName($sql, $idFieldName);
        }

        if ($isIdAutoincrement) {
            return (int) $this->adapter->getDriver()->getLastGeneratedValue($seqName);
        } else {
            return (int) $vars[$idFieldName];
        }
    }

    /**
     * @param string $sql
     * @param string $idFieldName
     * @return string|null
     */
    protected function getPgSeqName($sql, $idFieldName)
    {
        $name = $this->getTableName($sql);
        if (!$name) {
            return null;
        }
        return $name . '_' . $idFieldName . '_' . 'seq';
    }

    /**
     * @param $sql
     * @return string|null
     */
    protected function getTableName($sql)
    {
        $splitSQL = explode(' ', $sql);
        $result = null;
        for ($i = 0; $i < count($splitSQL); $i++)
        {
            if (strtolower($splitSQL[$i]) === 'insert')
            {
                if (strtolower($splitSQL[$i+1]) === 'into')
                {
                    $result = $splitSQL[$i+2];
                    break;
                }
            }
        }
        $result = explode('(', $result)[0];
        $name = str_replace(['"', "'", '\\'], "", $result);

        return $name;
    }

    public function delete($sql, $vars)
    {
        $stmt = $this->query($sql, $vars);
        $data = $stmt->count();

        return $data;
    }

    /**
     * @throws TransactionException
     */
    public function beginTransaction()
    {
        if ($this->isTransactionActive === true) {
            throw new TransactionException("Can't begin transaction while another one is running");
        }

        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();
            $this->isTransactionActive = true;
        } catch (\Exception $e) {
            throw new TransactionException('Can`t begin transaction', 0, $e);
        }
    }

    /**
     * @return bool
     */
    public function isTransaction()
    {
        return $this->isTransactionActive;
    }

    /**
     * @throws TransactionException
     */
    public function commitTransaction()
    {
        if ($this->isTransactionActive === false) {
            throw new TransactionException("Can't commit transaction while no one is running");
        }

        try {
            $this->adapter->getDriver()->getConnection()->commit();
            $this->isTransactionActive = false;
        } catch (\Exception $e) {
            throw new TransactionException('Can`t commit transaction', 0, $e);
        }
    }

    /**
     * @throws TransactionException
     */
    public function rollbackTransaction()
    {
        if ($this->isTransactionActive === false) {
            throw new TransactionException("Can't rollback transaction while no one is running");
        }

        try {
            $this->adapter->getDriver()->getConnection()->rollBack();
            $this->isTransactionActive = false;
        } catch (\Exception $e) {
            throw new TransactionException("Can`t rollback transaction", 0, $e);
        }
    }

    public function setProfiler($profiler)
    {
        $this->adapter->setProfiler($profiler);
    }
}
