<?php
namespace SFM\Database;

class Config
{
    protected $driver;
    protected $host;
    protected $port;
    protected $user;
    protected $pass;
    protected $db;
    protected $initialQueries = array();

    /**
     * @param string $db
     * @return $this
     */
    public function setDb($db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * @return string
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param string $driver
     * @return $this
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @return string
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param string $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $port
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @param string[] $initialQueries
     * @return $this
     */
    public function setInitialQueries($initialQueries)
    {
        $this->initialQueries = $initialQueries;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getInitialQueries()
    {
        return $this->initialQueries;
    }

    /**
     * @param string $pass
     * @return $this
     */
    public function setPass($pass)
    {
        $this->pass = $pass;

        return $this;
    }

    /**
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @param string $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }
}