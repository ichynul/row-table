<?php

namespace Ichynul\RowTable;

use Encore\Admin\Form;
use Encore\Admin\Form\Field;
use Encore\Admin\Form\Field\Html;
use Encore\Admin\Widgets\Table as TableView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;

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
        $this->tableWidget = new TableView(['Empty table'], []);
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
     * Set original value to the field.
     *
     * @param array $data
     *
     * @return void
     */
    public function setOriginal($data)
    {
        $data = Input::all();
        $rows = $this->rows;
        $fields = [];
        $this->form->saving(function (Form $form) use ($rows, $data) {
            foreach ($rows as &$row) {
                $fields = $row->geFields();
                foreach ($fields as $field) {
                    $field->setOriginal($data);
                }
            }

            if ($validationMessages = $this->validationMessages($data)) {
                \Log::info('error');
                return back()->withInput()->withErrors($validationMessages);
                $error = new MessageBag([
                    'title'   => 'xxxx',
                    'message' => '0000',
                ]);
                return back()->with(compact('error'));
            }
            $error = new MessageBag([
                'title'   => 'title...',
                'message' => 'message....',
            ]);
        
            return back()->with(compact('error'));
        });
    }

    public function validationMessages($input)
    {
        $failedValidators = [];
        \Log::info($input);
        foreach ($this->rows as $row) {
            $columns = [];
            foreach ($row->geFields() as $field) {
                if (!$validator = $field->getValidator($input)) {
                    continue;
                }
                if (($validator instanceof Validator) && !$validator->passes()) {
                    $failedValidators[] = $validator;
                }
            }
        }
        $message = $this->mergeValidationMessages($failedValidators);
        return $message->any() ? $message : false;
    }

    /**
     * Merge validation messages from input validators.
     *
     * @param \Illuminate\Validation\Validator[] $validators
     *
     * @return MessageBag
     */
    protected function mergeValidationMessages($validators)
    {
        $messageBag = new MessageBag();
        foreach ($validators as $validator) {
            $messageBag = $messageBag->merge($validator->messages());
        }
        return $messageBag;
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