<?php

namespace SFM\Criteria;

abstract class AbstractCriteria
{
    const DIRECTION_DESC = 'DESC';
    const DIRECTION_ASC = 'ASC';

    /**
     * Поле сортировки
     * @var array
     */
    protected $_sort;

    /**
     * Направление сортировки
     * @var array
     */
    protected $_direction;

    /**
     * Текущая страница
     * @var integer
     */
    protected $_page;

    /**
     * Количество на страницу
     * @var integer
     */
    protected $_perPage;

    /**
     * @return int $_page
     */
    public function getPage()
    {
        return $this->_page;
    }

    /**
     * @return int $_perPage
     */
    public function getPerPage()
    {
        return $this->_perPage;
    }

    /**
     * @param integer $page
     * @return AbstractCriteria
     */
    public function setPage($page)
    {
        $this->_page = $page;
        return $this;
    }

    /**
     * @param integer $perPage
     * @return AbstractCriteria
     */
    public function setPerPage($perPage)
    {
        $this->_perPage = $perPage;
        return $this;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->_direction;
    }

    /**
     * @param string $dir
     * @return AbstractCriteria
     */
    public function setDirection($dir)
    {
        $this->_direction = $dir;
        return $this;
    }

    /**
     * @return string
     */
    public function getSort()
    {
        return $this->_sort;
    }

    /**
     * @param int $sort
     * @return $this
     */
    public function setSort($sort)
    {
        $this->_sort = $sort;
        return $this;
    }
} 