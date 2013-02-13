<?php
interface SFM_Entity_HandlerInterface
{
    /**
     * Handle data processing
     * @param array $data Source data
     * @param array $bindings Database fields to array keys binding
     * @return array Filtered data
     */
    public function handle(array $data, $bindings = array());

    /**
     * Handle data filtering
     * @param array $data Source data
     * @param array $bindings Database fields to array keys binding
     * @return array Filtered data
     */
    public function filter(array $data, $bindings = array());

    /**
     * Handle data validation
     * @param array $data Source data
     * @param array $bindings Database fields to array keys binding
     * @return array Errors
     */
    public function validate(array $data, $bindings = array());

    /**
     * Add filter
     * @param string $field
     * @param Zend_Filter $filter
     * @return static
     */
    public function addFilter($field, $filter);

    /**
     * Add filters
     * @param array $fields Filters config
     * @return static
     */
    public function addFilters($fields = array());

    /**
     * Add validator
     * @param string $field
     * @param Zend_Validate $validator
     * @return static
     */
    public function addValidator($field, $validator);

    /**
     * Add validators
     * @param array $fields Validators config
     * @return static
     */
    public function addValidators($fields = array());

    /**
     * @param array $fields
     * @return static
     */
    public function addRequiredFields($fields = array());

    /**
     * @param string $field
     * @return static
     */
    public function addRequiredField($field);

    /**
     * Get errors
     * @return array
     */
    public function getErrors();
}