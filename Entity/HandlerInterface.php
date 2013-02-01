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
     * Add filters
     * @param array $fields Filters config
     * @return static
     */
    public function addFilters($fields = array());

    /**
     * Add validators
     * @param array $fields Validators config
     * @return static
     */
    public function addValidators($fields = array());

    /**
     * Get errors
     * @return array
     */
    public function getErrors();
}