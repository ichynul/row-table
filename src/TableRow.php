<?php

namespace Ichynul\RowTable;

use Encore\Admin\Form;
use Encore\Admin\Form\Field;
use Illuminate\Support\Arr;

/**
 * Class TableRow.
 * 
 * Copy from Encore\Admin\Form;
 * 
 * @method Field\Text           text($column, $label = '', $colspan = 1)
 * @method Field\Checkbox       checkbox($column, $label = '', $colspan = 1)
 * @method Field\Radio          radio($column, $label = '', $colspan = 1)
 * @method Field\Select         select($column, $label = '', $colspan = 1)
 * @method Field\MultipleSelect multipleSelect($column, $label = '', $colspan = 1)
 * @method Field\Textarea       textarea($column, $label = '', $colspan = 1)
 * @method Field\Hidden         hidden($column, $label = '', $colspan = 1)
 * @method Field\Id             id($column, $label = '', $colspan = 1)
 * @method Field\Ip             ip($column, $label = '', $colspan = 1)
 * @method Field\Url            url($column, $label = '', $colspan = 1)
 * @method Field\Color          color($column, $label = '', $colspan = 1)
 * @method Field\Email          email($column, $label = '', $colspan = 1)
 * @method Field\Mobile         mobile($column, $label = '', $colspan = 1)
 * @method Field\Slider         slider($column, $label = '', $colspan = 1)
 * @method Field\File           file($column, $label = '', $colspan = 1)
 * @method Field\Image          image($column, $label = '', $colspan = 1)
 * @method Field\Date           date($column, $label = '', $colspan = 1)
 * @method Field\Datetime       datetime($column, $label = '', $colspan = 1)
 * @method Field\Time           time($column, $label = '', $colspan = 1)
 * @method Field\Year           year($column, $label = '', $colspan = 1)
 * @method Field\Month          month($column, $label = '', $colspan = 1)
 * @method Field\DateRange      dateRange($start, $end, $label = '', $colspan = 1)
 * @method Field\DateTimeRange  datetimeRange($start, $end, $label = '', $colspan = 1)
 * @method Field\TimeRange      timeRange($start, $end, $label = '', $colspan = 1)
 * @method Field\Number         number($column, $label = '', $colspan = 1)
 * @method Field\Currency       currency($column, $label = '', $colspan = 1)
 * @method Field\HasMany        hasMany($relationName, $label = '', $callback)
 * @method Field\SwitchField    switch($column, $label = '', $colspan = 1)
 * @method Field\Display        display($column, $label = '', $colspan = 1)
 * @method Field\Rate           rate($column, $label = '', $colspan = 1)
 * @method Field\Divider        divider($title = '')
 * @method Field\Password       password($column, $label = '', $colspan = 1)
 * @method Field\Decimal        decimal($column, $label = '', $colspan = 1)
 * @method Field\Html           html($html, $label = '', $colspan = 1)
 * @method Field\Tags           tags($column, $label = '', $colspan = 1)
 * @method Field\Icon           icon($column, $label = '', $colspan = 1)
 * @method Field\Embeds         embeds($column, $label = '', $callback)
 * @method Field\MultipleImage  multipleImage($column, $label = '', $colspan = 1)
 * @method Field\MultipleFile   multipleFile($column, $label = '', $colspan = 1)
 * @method Field\Captcha        captcha($column, $label = '', $colspan = 1)
 * @method Field\Listbox        listbox($column, $label = '', $colspan = 1)
 * @method Field\Table          table($column, $label, $builder)
 * @method Field\Timezone       timezone($column, $label = '', $colspan = 1)
 * @method Field\KeyValue       keyValue($column, $label = '', $colspan = 1)
 * @method Field\ListField      list($column, $label = '', $colspan = 1)
 */

class TableRow
{
    /**
     * @var array
     */
    protected $col_spans = [];

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var boolean
     */
    protected $bind_rows = false;

    /**
     * @var FormTable
     */
    protected $table = false;

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
    public function pushField(Field $field, $colspan = 1)
    {
        $column = $this->columnStr($field->column());

        $label = $field->label();

        if ($this->bind_rows) {

            admin_error('Error', "Don not pushField '{$label}' after \$table->setRows(\$tableRow) was called!");

            return $this;
        }

        $this->fields[] = $field;

        $this->col_spans[$column] = is_numeric($colspan) ? $colspan : 1;

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

        if ($defaults == $rows) {

            $this->col_spans = [];

            foreach ($this->fields as $field) {

                $column = $this->columnStr($field->column());

                if ($rows >= 4) {

                    $this->col_spans[$column] = 3;

                    $field->setWidth(6, 6);
                } else if ($rows == 3) {

                    $this->col_spans[$column] = 4;

                    $field->setWidth(8, 4);
                } else if ($rows == 2) {

                    $this->col_spans[$column] = 6;

                    $field->setWidth(8, 2);
                } else {

                    $this->col_spans[$column] = 12;

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
    public function getSpan($columns)
    {
        $columns = $this->columnStr($columns);

        if (empty($this->col_spans)) {
            return 1;
        }

        if (!array_key_exists($columns, $this->col_spans)) {
            return 1;
        }

        return $this->col_spans[$columns] ?: 1;
    }

    /**
     * set col_spans
     *
     * @return $this
     */
    public function setcolspans(array $col_spans)
    {
        $this->col_spans = $col_spans;
        return $this;
    }

    /**
     * set table
     *
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * get column as string
     *
     * @param string|array $columns
     * @return string
     */
    public function columnStr($columns)
    {
        $key = $columns;

        if (is_array($columns)) { //Elements has more than 2 arguments : [dateRange / datetimeRange / latlong] , they column is array;

            $key = implode('_', array_values($columns));
        }

        return $key;
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

            $column = Arr::get($arguments, 0, '');

            $arguments = array_slice($arguments, 1);

            $count = count($arguments);

            $colspan = 1;

            if ($count > 0) {
                // $form->text($column, $label); Most of the elements has 2 arguments;
                // $form->dateRange($startDate, $endDate, $label); //Elements has more than 2 arguments : [dateRange / datetimeRange / latlong];

                // last of arguments is label
                $label = $arguments[$count - 1];

                if (is_numeric($label)) { // if the last is number

                    //Exemple:
                    // $form->text($column, $label, 2);  // 2 is colspan
                    // $form->dateRange($startDate, $endDate, $label, 2);

                    $colspan = intval($label);
                }
            }

            $element = new $className($column, $arguments);

            if (!$this->bind_rows) {

                $element->setWidth(8, 4);

                $this->pushField($element, $colspan);

                return $element;
            }

            $args = $label ? "'{$column}', '$label'" : "$column";

            admin_error('Error', "Don not call \$tableRow->{$method}('{$args}') after \$table->setRows(\$tableRow) was called!");

            return new Field\Nullable();
        }

        admin_error('Error', "Field type [$method] does not exist.");

        return new Field\Nullable();
    }
}
