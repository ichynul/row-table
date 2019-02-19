# laravel-admin row-table

## Installation

Run :

```
$ composer require ichynul/row-table
```

Then run:

```
$ php artisan vendor:publish --tag=row-table

```

## Usage

```php
protected function form()
{
    $form = new Form(new Task);

    $headers = ['备注', '服务费用', '服务评分'];
    $tableRow = new TableRow();
    $tableRow->textarea('remark', '备注')->rules('required');
    $tableRow->text('fee', '服务费用')->rules('required');
    $tableRow->number('rating', '服务评分')->max(5)->min(1);


    $headers2 = ['地址', '评价', '图片'];
    $tableRow2 = new TableRow();
    $tableRow2->text('address', '地址')->rules('required');
    $tableRow2->text('comment', '评价');
    $tableRow2->text('username', '姓名');
    /**
     * 理论上可使用任意Form组件，从最终显示效果考虑table还是用 text 比较合适
     * $form->text($column, [$label]);
     * useDiv(true) 使用div时 需要 $label
     * useDiv(false)默认table时 $label 不需要，不显示
     * 
     * table 时建议 一个table + 一个 $headers + 一个  tableRow :
     * ----------------------------------------
     * label1-1 | label1-2 | label1-3 | label1-4
     * ----------------------------------------
     * value1-1 | value1-2 | value1-3 | value1-4 
     * ----------------------------------------
     * label2-1 | label2-2 | label2-3 | label2-4
     * ----------------------------------------
     * value2-1 | value2-2 | value2-3 | value2-4 
     * ----------------------------------------
     * 或者 第一个table设置头部，下面的不设置，共用 ：
     * ----------------------------------------
     * label1-1 | label1-2 | label1-3 | label1-4
     * ----------------------------------------
     * value1-1 | value1-2 | value1-3 | value1-4 
     * ----------------------------------------
     * value2-1 | value2-2 | value2-3 | value2-4 
     * ----------------------------------------
     * value3-1 | value3-2 | value3-3 | value3-4 
     * ----------------------------------------
     * div 模式用不用区别不大
     */

    $form->table($form, '任务信息1')
        ->setHeaders($headers)
        ->setRows($tableRow)//一个row 
        //->setRows([$tableRow, $tableRow2])
        ->useDiv(true) //使用div显示，默认table ,div效果不怎么好
        ->headersTh(true);//[table]头部使用<th></th>，默认使用<td></td>样式有些差别
        //->getTableWidget()//extends Encore\Admin\Widgets\Table
        //->offsetSet("style", "width:1000px;");
    $form->table($form, '任务信息2')
        ->setHeaders($headers2)
        ->setRows($tableRow2);
        //使用div显示，默认table ,div效果不怎么好;

    $tableRow->radio('status', '任务状态')->options(Task::$statusMap)->attribute(['readonly' => 'readonly']);
    $form->select('rec_user_id', '任务接单人')->options(function ($id) {
        $user = User::find($id);
        if ($user) {
            return [$user->id => $user->name];
        }
    })->ajax('/admin/api/users');
    $form->radio('type', '任务类型')->options(Task::$typeMap)->attribute(['readonly' => 'readonly']);

    $form->display('created_at', trans('admin.created_at'));
    $form->display('updated_at', trans('admin.updated_at'));
    $form->tools(function (Form\Tools $tools) {
        // 去掉`删除`按钮
        $tools->disableDelete();
        // 去掉`查看`按钮
        $tools->disableView();
    });

    $form->footer(function ($footer) {
        // 去掉`查看`checkbox
        $footer->disableViewCheck();
        // 去掉`继续创建`checkbox
        $footer->disableCreatingCheck();
    });

    return $form;
}
```

License

---

Licensed under [The MIT License (MIT)](LICENSE).
