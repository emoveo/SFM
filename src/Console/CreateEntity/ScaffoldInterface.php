<?php
namespace SFM\Console\CreateEntity;

interface ScaffoldInterface
{
    /**
     * @param string $table
     */
    public function __construct($table);

    /**
     * @return string
     */
    public function getScaffold();

    /**
     * @return string
     */
    public function getFilename();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getClass();
}