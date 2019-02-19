<?php

namespace Ichynul\RowTable;

use Encore\Admin\Form;
use Encore\Admin\Form\Field;
use Encore\Admin\Form\Field\MultipleSelect;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Validator as ValidatorTool;
use Illuminate\Validation\Validator;

class Table extends Field
{
    /**
     * @var array
     */
    protected static $css = [
        'vendor/laravel-admin-ext/row-table/table.css',
    ];

    /**
     * @var TableWidget
     */
    protected $tableWidget = null;

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

    public function __construct($form, $label)
    {
        $this->form = $form;
        $this->tableWidget = new TableWidget([], []);
        $this->tableWidget->class($this->defaultClass);
        $this->callSubmitted();
        parent::__construct('', $label);
    }

    /**
     * Call submitted callback.
     *
     * @return mixed
     */
    protected function callSubmitted()
    {
        $this->form->submitted(function (Form $form) {
            \Log::info('submitted');
            foreach ($this->rows as $row) {
                foreach ($row->geFields() as $field) {
                   // $this->form->builder()->fields()->push($field);
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
    public function useDiv($div)
    {
        $this->tableWidget->useDiv($div);
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
        $this->tableWidget->headersTh($th);
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
        $this->tableWidget->setHeaders($headers);
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
            foreach ($row->geFields() as $field) {
                $formatId .= $this->formatId($field->column());
            }
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
        $this->tableWidget->setStyle($style);
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
        $this->tableWidget->class($this->defaultClass . implode(' ', $style));
        return $this;
    }

    /**
     * get inner tableWidget
     *
     * @return TableWidget
     */
    public function getTableWidget()
    {
        return $this->tableWidget;
    }

    protected function buildFields()
    {
        $tableRows = [];
        $data = $this->form->model()->toArray();
        foreach ($this->rows as $row) {
            $columns = [];
            foreach ($row->geFields() as $field) {
                if (!$field instanceof Field) {
                    $tableRows[] = $field;
                    throw new \Exception('Column format error! Column must be a instanceof Encore\Admin\Form\Field');
                }
                $field->fill($data);
                if (!$this->tableWidget->usingDiv()) {
                    $field->setWidth(12, 0);
                    $field->attribute(['title' => $field->column()]);
                }
                $columns[] = $field->render();
            }
            $tableRows[] = $columns;
        }
        $this->tableWidget->setRows($tableRows);
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
        return false;
    }

    protected function makeValidator($field, $validator)
    {
        $err = $validator->errors()->first($field->getErrorKey());
        $column = $field->column();
        if (is_string($column)) {
            if (!array_has($this->input, $column)) {
                return;
            }
            $this->input = $this->sanitizeInput($this->input, $column);
            $this->allRules[$field->getErrorKey()] = ['required'];
            $this->messages[$field->getErrorKey() . '.required'] = $err;
            $this->labels[$field->getErrorKey()] = $field->label();
        } else if (is_array($column)) {
            foreach ($column as $key => $col) {
                if (!array_key_exists($col, $this->input)) {
                    return;
                }
                $this->input[$column . $key] = array_get($this->input, $column);
                $this->allRules[$column . $key] = $fieldRules;
                $this->messages[] = [$field->getErrorKey() . '.required' => $err];
                $this->labels[$column . $key] = $this->label . "[$column]";
            }
        }
    }

    /**
     * Sanitize input data.
     *
     * @param array  $input
     * @param string $column
     *
     * @return array
     */
    protected function sanitizeInput($input, $column)
    {
        if ($this instanceof MultipleSelect) {
            $value = array_get($input, $column);
            array_set($input, $column, array_filter($value));
        }
        return $input;
    }

    /**
     * Render this filed.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $this->buildFields();
        $this->addVariables([
            'table' => $this->tableWidget->render()
        ]);
        return view($this->getView(), $this->variables());
    }
}