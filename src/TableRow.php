<?php

namespace Ichynul\RowTable;

use Closure;
use Encore\Admin\Form;
use Encore\Admin\Extension;
use Encore\Admin\Form\Field;
use Encore\Admin\Form\Field\Html;

class TableRow
{
    /**
     * @var array
     */
    protected $rwo_spans = [];

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var boolean
     */
    protected $bind_rows = false;

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
    public function pushField(Field $field, $row_span)
    {
        $column = $field->column();

        $label = $field->label();

        if ($this->bind_rows) {

            admin_error('Error', "Don not pushField '{$label}' after \$table->setRows(\$tableRow) was called!");

            return $this;
        }

        $this->fields[] = $field;

        $this->rwo_spans[$field->column()] = is_numeric($row_span) ? $row_span : 1;

        return $this;
    }

    /**
     * @return $this
     */
    public function bindRows()
    {
        $this->bind_rows = true;

        return $this;
    }

    /**
     * Auto set Width 
     * @return $this
     */
    public function autoSpan()
    {
        $defaults = 0;

        $use = 0;

        foreach ($this->fields as $field) {

            $width = $this->getSpan($field->column());

            if ($width == 1) {

                $defaults += 1;
            }

            $use += $width;
        }

        $rows = count($this->fields);

        if ($use < 10 || $defaults == $rows) {

            $this->rwo_spans = [];

            foreach ($this->fields as $field) {

                if ($rows >= 4) {

                    $this->rwo_spans[$field->column()] = 3;

                    $field->setWidth(7, 5);

                } else if ($rows == 3) {

                    $this->rwo_spans[$field->column()] = 4;

                    $field->setWidth(8, 4);

                } else if ($rows == 2) {

                    $this->rwo_spans[$field->column()] = 6;

                    $field->setWidth(9, 3);

                } else {

                    $this->rwo_spans[$field->column()] = 12;

                    $field->setWidth(10, 2);
                }
            }
        }

        return $this;
    }

    /**
     * get rwo_span of column
     *
     * @return int
     */
    public function getSpan($column)
    {
        if (empty($this->rwo_spans)) {
            return 1;
        }

        if (!array_key_exists($column, $this->rwo_spans)) {
            return 1;
        }

        return $this->rwo_spans[$column] ? : 1;
    }

    /**
     * set rwo_spans 
     *
     * @return $this
     */
    public function setRowspans(array $rwo_spans)
    {
        $this->rwo_spans = $rwo_spans;
        return $this;
    }

    /**
     * call methd each rows
     * 
     * @return $this
     */
    public function each(Closure $callback)
    {
        foreach ($this->fields as $field) {
            $callback->call($this, $field);
        }

        return $this;
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

            if (!$this->bind_rows) {

                $rowspan = count($arguments) > 2 ? array_get($arguments, 2, 1) : array_get($arguments, 1, 1);

                $this->pushField($element, $rowspan);

                return $element;
            }

            $label = array_get($arguments, 1, '');

            $args = $label ? "'{$column}', '$label'" : "$column";

            admin_error('Error', "Don not call \$tableRow->{$method}('{$args}') after \$table->setRows(\$tableRow) was called!");

            return new Field\Nullable();
        }

        admin_error('Error', "Field type [$method] does not exist.");

        return new Field\Nullable();
    }
}