<?php

namespace Ichynul\RowTable;

use Encore\Admin\Form;
use Encore\Admin\Form\Field;
use Encore\Admin\Form\Field\Html;
use Encore\Admin\Widgets\Table as TableView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Validator;

class Table extends Html
{
    /**
     * @var array
     */
    protected static $css = [
        'vendor/laravel-admin-ext/row-table/table.css',
    ];

    /**
     * @var TableView
     */
    protected $tableWidget = null;

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var TableRow
     */
    protected $errorRow = null;

    protected $display = null;

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

    public function __construct($form, $label)
    {
        $this->form = $form;
        $this->tableWidget = new TableView([], []);
        $this->tableWidget->class($this->defaultClass);
        parent::__construct('', $label);
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
        if (!is_array($rows)) {
            throw new \Exception('Rows format error!');
        }
        $this->rows = $rows;
        $this->addErrorRow();
        $formatId = '';
        foreach ($this->rows as $row) {
            foreach ($row->geFields() as $field) {
                $formatId .= $this->formatId($field->column());
            }
        }
        if (strlen($formatId) > 10) {
            $formatId = substr($formatId, 10);
        }
        $this->setErrorKey($formatId);
        \Log::info($formatId);
        return $this;
    }

    protected function addErrorRow()
    {
        $errorRow = new TableRow();
        $this->text = $errorRow->text('__table_error__', 'error');
        //$this->text->setForm($this->form);
        $this->rows[] = $errorRow;
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
     * @return TableView
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
                    throw new \Exception('Column format error! Column must be a instanceof Encore\Admin\Form\Field');
                }
                $field->fill($data);
                $columns[] = $field->render();
            }
            $tableRows[] = $columns;
        }
        $this->tableWidget->setRows($tableRows);
    }

    /**
     * Prepare for a field value before update or insert.
     *
     * @param $data
     *
     * @return mixed
     */
    public function prepare($data)
    {
        $fields = [];
        $this->form->saving(function (Form $form) use ($data) {
            foreach ($this->rows as &$row) {
                $fields = $row->geFields();
                foreach ($fields as $field) {
                    $field->setOriginal($data);
                }
            }

            if ($validationMessages = $this->validationMessages($data)) {
                \Log::info('error');
                return back()->withInput()->withErrors($validationMessages);
                $error = new MessageBag([
                    'title' => 'xxxx',
                    'message' => '0000',
                ]);
                return back()->with(compact('error'));
            }
            $error = new MessageBag([
                'title' => 'title...',
                'message' => 'message....',
            ]);

            return back()->with(compact('error'));
        });
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
        foreach ($this->rows as $row) {
            foreach ($row->geFields() as $field) {
                if (!$validator = $field->getValidator($input)) {
                    continue;
                }
                if (($validator instanceof Validator) && !$validator->passes()) {
                    $this->errorValidator = $validator;
                    $err = $validator->errors()->first($field->getErrorKey());
                    \Log::debug('$getmessages:' . $err);
                    Validator::make($input, [
                        'comment' => ['required', 'string'],
                        'rating' => ['required', 'integer', 'min:1', 'max:5'],
                    ], [
                        'comment.required' => '请输入评价内容',
                        'rating.required' => '请选择评分',
                        'rating.min' => '评分不能小于1',
                        'rating.max' => '评分不能大于5',
                    ]);
                    //return Validator::make($input, $rules, $this->validationMessages, $attributes);
                    //$this->errorRow->setErrorKey($field->getErrorKey());
                    return $this->errorValidator;
                }
            }
        }
        return false;
    }

    /**
     * Render this filed.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $this->plain = false;
        $this->buildFields();
        $this->html = $this->tableWidget->render();
        return parent::render();
    }
}