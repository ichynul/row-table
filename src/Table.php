<?php

namespace Ichynul\RowTable;

use Encore\Admin\Form;
use Encore\Admin\Form\Field;
use Illuminate\Validation\Validator;
use Encore\Admin\Form\Field\MultipleSelect;
use Illuminate\Support\Facades\Validator as ValidatorTool;
use Ichynul\RowTable\Field\Show;

class Table extends Field
{
    /**
     * @var array
     */
    protected static $css = [
        'vendor/laravel-admin-ext/row-table/table.css',
    ];

    /**
     * @var FromTable
     */
    protected $fromTable = null;

    /**
     * @var string
     */
    protected $view = 'row-table::table';

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * class attr
     *
     * @var string
     */
    protected $defaultClass = 'table table-fields ';

    /**
     * @var array
     */
    protected $rows = [];

    /**
     * @var array
     */
    protected $allRules = [];

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var array
     */
    protected $labels = [];

    /**
     * @var array
     */
    protected $input = [];

    public function __construct($label, $arguments = [])
    {
        $this->label = $label;

        $this->column = '__tabel__';

        $this->fromTable = new FromTable([], []);

        $this->fromTable->class($this->defaultClass);

        $func =  array_get($arguments, 0, null);
        
        if ($func && $func instanceof Closure) {

            call_user_func($func, $this);
        }
    }

    /**
     * Call submitted callback.
     *
     * @return mixed
     */
    protected function bindSubmitted()
    {
        $this->form->submitted(function (Form $form) {

            $this->form->ignore($this->column);

            foreach ($this->rows as $row) {

                foreach ($row->geFields() as $field) {

                    $this->form->builder()->fields()->push($field);
                }
            }
        });
    }

    /**
     * Set useDiv
     *
     * @param boolean $useDiv
     *
     * @return $this
     */
    public function useDiv($div = true)
    {
        $this->fromTable->useDiv($div);

        return $this;
    }

    /**
     * Set table whether th headers.
     *
     * @param boolean $th
     *
     * @return $this
     */
    public function headersTh($th = true)
    {
        $this->fromTable->headersTh($th);

        return $this;
    }

    /**
     * Set table headers.
     *
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders($headers = [])
    {
        $this->fromTable->setHeaders($headers);

        return $this;
    }

    /**
     * @param Form $form
     *
     * @return $this
     */

    public function setForm(Form $form = null)
    {
        $this->form = $form;

        $this->bindSubmitted();

        return $this;
    }

    /**
     * Set table rows.
     *
     * @param array $rows
     *
     * @return $this
     */
    public function setRows($rows = [])
    {
        if (!is_array($rows) && !$rows instanceof TableRow) {
            throw new \Exception('Rows format error!');
        }

        $this->rows = $rows instanceof TableRow ? [$rows] : $rows;

        $formatId = '';

        foreach ($this->rows as $row) {

            $row->setTable($this->fromTable);

            foreach ($row->geFields() as $field) {

                $field->setForm($this->form);

                $formatId .= $this->formatId($field->column());
            }

            $row->bindRows();
        }

        if (strlen($formatId) > 20) {
            $formatId = substr($formatId, 20);
        }

        $this->setErrorKey($formatId);

        $this->id = $formatId;

        return $this;
    }

    /**
     * Set table style.
     *
     * @param array $style
     *
     * @return $this
     */
    public function tableStyle($style = [])
    {
        $this->fromTable->setStyle($style);

        return $this;
    }

    /**
     * Set table class style.
     *
     * @param array $style
     *
     * @return $this
     */
    public function tableClass($style = [])
    {
        $this->fromTable->class($this->defaultClass . implode(' ', $style));
        return $this;
    }

    /**
     * get inner FromTable
     *
     * @return FromTable
     */
    public function getFromTable()
    {
        return $this->fromTable;
    }

    /**
     * Get validator for this field.
     *
     * @param array $input
     *
     * @return bool|Validator
     */
    public function getValidator(array $input)
    {
        if ($this->validator) {
            return $this->validator->call($this, $input);
        }
        $this->input = $input;

        $this->allRules = [];

        $this->messages = [];

        $this->labels = [];

        foreach ($this->rows as $row) {

            foreach ($row->geFields() as $field) {

                if (!$validator = $field->getValidator($this->input)) {
                    continue;
                }

                if (($validator instanceof Validator) && !$validator->passes()) {
                    $this->makeValidator($field, $validator);
                }
            }
        }

        if (empty($this->allRules)) {
            return false;
        }

        $this->allRules[$this->getErrorKey()] = ['required'];

        $this->messages[$this->getErrorKey() . '.required'] = implode(' ', array_values($this->messages));

        $this->labels[$this->getErrorKey()] = $this->label();

        return ValidatorTool::make($this->input, $this->allRules, $this->messages, $this->labels);
    }

    protected function makeValidator($field, $validator)
    {
        $err = $validator->errors()->first($field->getErrorKey());

        $column = $field->column();

        if (is_string($column)) {

            if (!array_has($this->input, $column)) {
                return;
            }

            $this->input = $this->sanitizeFieldInput($field, $this->input, $column);

            $this->allRules[$field->getErrorKey()] = ['required'];

            $this->messages[$field->getErrorKey() . '.required'] = $err;

            $this->labels[$field->getErrorKey()] = $field->label();
        } else if (is_array($column)) {

            foreach ($column as $key => $col) {

                if (!array_key_exists($col, $this->input)) {
                    return;
                }

                $this->input[$column . $key] = array_get($this->input, $column);

                $this->allRules[$column . $key] =  ['required'];

                $this->messages[] = [$field->getErrorKey() . '.required' => $err];

                $this->labels[$column . $key] = $this->label . "[$column]";
            }
        }
    }

    /**
     * Sanitize input data.
     * @param array  $field
     * @param array  $input
     * @param string $column
     *
     * @return array
     */
    protected function sanitizeFieldInput($field, $input, $column)
    {
        if ($field instanceof MultipleSelect) {

            $value = array_get($input, $column);

            array_set($input, $column, array_filter($value));
        }

        return $input;
    }

    /**
     * Fill data to the field.
     *
     * @param array $data
     *
     * @return void
     */
    public function fill($data)
    {
        foreach ($this->rows as $row) {

            foreach ($row->geFields() as $field) {

                $field->fill($data);
            }
        }
    }

    /**
     * Build fields
     *
     * @return void
     */
    protected function buildRows()
    {
        foreach ($this->rows as $row) {

            foreach ($row->geFields() as $field) {

                if (!$this->fromTable->usingDiv()) {

                    if (!$field instanceof Show) {
                        $field->setLabelClass(['hidden'])->attribute(['title' => $field->label()]);
                    }

                    $field->setWidth(12, 0);
                } else {

                    $row->autoSpan();
                }
            }
        }

        $this->fromTable->setRows($this->rows);
    }

    /**
     * Render this filed.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $this->buildRows();

        $this->addVariables([
            'table' => $this->fromTable->render()
        ]);

        return view($this->getView(), $this->variables());
    }
}
