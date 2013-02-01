<?php
class SFM_Entity_Handler implements SFM_Entity_HandlerInterface
{
    /** @var Zend_Filter[] */
    protected $filters = array();

    /** @var Zend_Validate[] */
    protected $validators = array();

    /** @var string[] */
    protected $errors = array();

    protected $alreadyServedContext = array();

    /**
     * @param string $field
     * @param Zend_Filter $filter
     * @return static
     */
    protected function addFilter($field, $filter)
    {
        if (false === isset($this->filters[$field])) {
            $this->filters[$field] = array();
        }

        $this->filters[$field][] = $filter;

        return $this;
    }

    /**
     * @param array
     * @return static
     */
    public function addFilters($fields = array())
    {
        foreach ($fields as $field => $filters) {

            if (false === is_array($filters)) {
                $filters = array($filters);
            }

            foreach ($filters as $filter) {
                $this->addFilter($field, $filter);
            }
        }

        return $this;
    }

    /**
     * @param string $field
     * @param Zend_Validate $validator
     * @return static
     */
    protected function addValidator($field, $validator)
    {
        if (false === isset($this->filters[$field])) {
            $this->validators[$field] = array();
        }

        $this->validators[$field][] = $validator;

        return $this;
    }

    /**
     * @param array
     * @return static
     */
    public function addValidators($fields = array())
    {
        foreach ($fields as $field => $validators) {

            if (false === is_array($validators)) {
                $validators = array($validators);
            }

            foreach ($validators as $validator) {
                $this->addValidator($field, $validator);
            }
        }

        return $this;
    }

    /**
     * @param array $data
     * @param array $bindings
     * @return array
     */
    public function filter(array $data, $bindings = array())
    {
        foreach ($data as $field => $value) {

            $key = isset($bindings[$field]) ? $bindings[$field] : $field;

            if (isset ($this->filters[$key])) {

                /** @var $filter Zend_Filter */
                foreach ($this->filters[$key] as $filter) {
                    $value = $filter->filter($value);
                }
                $data[$field] = $value;
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @param array $bindings
     * @return array
     */
    public function validate(array $data, $bindings = array())
    {
        $errors = array();

        foreach ($data as $field => $value) {

            $key = isset($bindings[$field]) ? $bindings[$field] : $field;

            if (isset ($this->validators[$key])) {

                /** @var $validator Zend_Validate */
                foreach ($this->validators[$key] as $validator) {

                    if (false === $validator->isValid($value)) {

                        if (false === isset($errors[$field])) {
                            $errors[$field] = array();
                        }

                        $errors[$field] = array_merge($errors[$field], $validator->getMessages());
                    }

                }
                $data[$field] = $value;
            }

        }

        return $errors;
    }

    /**
     * Get data identity
     * @param array $data
     * @return string
     */
    protected function getIdentity($data)
    {
        $identity = hash('sha256', serialize($data));
        return $identity;
    }

    /**
     * @param array $data
     * @param array $bindings
     * @return array
     */
    public function handle(array $data, $bindings = array())
    {
        $identity = $this->getIdentity($data);
        $data     = $this->filter($data, $bindings);

        if (isset($this->alreadyServedContext[$identity])) {
            $errors = $this->alreadyServedContext[$identity];
        } else {
            $errors = $this->validate($data, $bindings);
            $this->alreadyServedContext[$identity] = $errors;
        }

        $this->errors = $errors;

        return $data;
    }

    public function getErrors()
    {
        return $this->errors;
    }

}