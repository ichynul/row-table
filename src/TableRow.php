<?php

namespace Ichynul\RowTable;

use Encore\Admin\Extension;
use Encore\Admin\Form;
use Encore\Admin\Form\Field;

class TableRow
{

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * Get rows
     *
     * @return array
     */
    public function geFields()
    {
        return $this->fields;
    }

    /**
     * @param Field $field
     *
     * @return $this
     */
    public function pushField(Field $field)
    {
        $this->fields[] = $field;
    }

    /**
     * Generate a Field object and add to form builder if Field exists.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return Field
     */
    public function __call($method, $arguments)
    {
        if ($className = Form::findFieldClass($method)) {
            $column = array_get($arguments, 0, ''); //[0];

            $element = new $className($column, array_slice($arguments, 1));

            $element->setWidth(12, 0);

            $this->pushField($element);

            return $element;
        }

        admin_error('Error', "Field type [$method] does not exist.");

        return new Field\Nullable();
    }
}