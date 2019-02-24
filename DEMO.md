```php
/**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        /**
         * textSmall => \Ichynul\RowTable\Field\TextSmall::class
         * show => \Ichynul\RowTable\Field\Show::class
         *
         ****************************************************************************************************
         * $tableRow-element($column, $label, $width); //div 时 class="col-sm-{$width}"
         *
         * $tableRow-element($column, $rowspan); // table 时 rowspan="{$width}"
         *
         * $tableRow-element($column); 都可以
         *
         * $tableRow-show($text, $width)->Textalign($align);  //div 时 class="col-sm-{$width}"
         *
         * $tableRow-show($text, $rowspan)->Textalign($align)->textWidth($textWidth);  //table 时  rowspan="{$width}";
         *
         * <div class="form-group" style="text-align:{$align};width:{$textWidth};min-width:100px;">
         *  <label style="text-align:{$align};" class="col-sm-{$width} control-label">{$text}</label>
         *</div>
         */

        $form = new Form(new User);

        $form->text('somerow', '混合使用form')->rules('required');

        $form->show("<h3>************Demo 1 , 使用 table************</h3>")->textWidth('100%')->Textalign('center');
        $form->divide();
        /*************************************/

        $months = ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'];
        $names = ['小刚', '小明', '小红', '张三'];
        $headers1 = [];
        $tablerows = [];
        /*************************************/
        $h = new Show('姓名\\月份');
        $headers1[] = $h->textWidth('120px')->render();

        foreach ($months as $month) {
            $h = new Show($month);
            $headers1[] = $h->render();
        }

        $tableRow = new TableRow();
        $tableRow->show('小刚')->textWidth('120px');

        $i = 0;
        $j = 0;

        foreach ($names as $name) {
            $row = new TableRow();
            $i += 1;
            $row->show($name);
            foreach ($months as $month) {
                $j += 1;
                $row->textSmall("table_1_{$i}_{$j}", $month . '工资')->rules(($i * $j) % 10 == 0 ? 'min:3' : '');
            }
            $tablerows[] = $row;
        }

        $form->table('工资情况')
            ->setHeaders($headers1)
            ->setRows($tablerows);

        /*************************************/

        $form->show("<h3>************Demo 2 , 使用 div************</h3>")->textWidth('100%')->Textalign('center');

        $form->icon('somerow2', '中间混合使用form')->rules('required');

        $form->divide();

        $i = 0;
        $j = 0;

        foreach ($names as $name) {
            $i += 1;
            $row = new TableRow();
            foreach ($months as $month) {
                $j += 1;
                if ($i % 3 == 0) {
                    $row->checkbox("table_2_{$i}_{$j}", $month . '工资', 6) //radio 比较占地方 col-sm-6,每行2列
                        ->options(['3000' => '￥3000', '4000' => '￥4000', '8000' => '￥8000']);
                } else if ($i % 4 == 0) {
                    $row->select("table_2_{$i}_{$j}", $month . '工资', 4) //col-sm-4,每行3列
                        ->rules(($i * $j) % 7 == 0 ? 'int:max10000' : '')
                        ->options(['3000' => '￥3000', '4000' => '￥4000', '8000' => '￥8000'])->setWidth(7, 4); //手动设置一下 label和 select 宽带
                } else {
                    $row->text("table_2_{$i}_{$j}", $month . '工资') //col-sm-3,每行4列 ，  未设置 且 cols > 4 ,自适应 每行4列
                        ->rules(($i * $j) % 8 == 0 ? 'required' : '');
                }
            }
            $form->table($name . '工资')
                ->setRows($row)
                ->useDiv(true);
            $form->divide();
        }

        /*************************************/
        $form->show("<h3>************Demo 3 , use div build a user center ************</h3>")->textWidth('100%')->Textalign('center');

        $userRow = new TableRow();
        $userRow->image('photo', '头像', 6)->value('/vendor/laravel-admin/AdminLTE/dist/img/default-50x50.gif')->removeable();
        $userRow->html('<span style="margin-top:10px;" class="label label-warning">没个性也签名~</span>', '个性签名', 6);

        $userRow1 = new TableRow();
        $userRow1->text('name', '姓名', 6)->rules('required');;
        $userRow1->radio('gender', '性别', 6)->options(['0' => '保密', '1' => '男', '2' => '女']);

        $userRow2 = new TableRow();
        $userRow2->number('age', '年龄', 6)->max(99)->min(18);
        $userRow2->date('birthday', '生日', 6)->rules('required');

        $userRow2->textarea('about', '个人简介', 12)->setWidth(10, 2); //独占一行，因为其他行有两列

        $form->table('个人中心', '11')
            ->setRows([$userRow, $userRow1, $userRow2])
            ->useDiv(true);

        $form->divide();
        /*************************************/
        $form->show("<h3>************Demo 4 , use table build a user center ************</h3>")->textWidth('100%')->Textalign('center');

        //这个比较麻烦,仅作为演示
        /*********************/
        $userRow3 = new TableRow();
        $userRow3->show('头像')->Textalign('left');
        $userRow3->show('个性签名')->Textalign('left');

        $userRow4 = new TableRow();
        $userRow4->image('photo')->default('/vendor/laravel-admin/AdminLTE/dist/img/default-50x50.gif');
        $userRow4->show('<span class="label label-info">没个性也签名~</span>')->Textalign('left');

        /*********************/
        $userRow5 = new TableRow();
        $userRow5->show('姓名')->Textalign('left');
        $userRow5->show('性别')->Textalign('left');

        $userRow6 = new TableRow();
        $userRow6->text('name');
        $userRow6->radio('gender')->options(['0' => '保密', '1' => '男', '2' => '女']);

        /*********************/
        $userRow7 = new TableRow();
        $userRow7->show('年龄')->Textalign('left');;
        $userRow7->show('生日')->Textalign('left');

        $userRow8 = new TableRow();
        $userRow8->number('age', '年龄')->max(99)->min(18);
        $userRow8->date('birthday', '生日');

        /*********************/
        $userRow9 = new TableRow();
        $userRow9->show('个人简介', 2)->Textalign('left'); // rowspan=2

        $userRow10 = new TableRow();
        $userRow10->textarea('about', 2); // rowspan=2

        /*********************/
        $form->table('个人中心2')
            ->setRows([$userRow3, $userRow4, $userRow5, $userRow6, $userRow7, $userRow8, $userRow9, $userRow10]);

        $form->divide();
        /*************************************/
        $form->show("<h3>************Demo 5 , table rowspan, ************</h3>")->textWidth('100%')->Textalign('center');

        $tableRow = new TableRow();

        $tableRow->text('row1'); //defautt 1
        $tableRow->text('row2');
        $tableRow->text('row3')->rules('required');;
        $tableRow->text('row4');

        $tableRow1 = new TableRow();
        $tableRow1->text('row5', 2);
        $tableRow1->text('row6', 2);

        $tableRow2 = new TableRow();
        $tableRow2->text('row7', 2);
        $tableRow2->text('row8'); //defautt 1
        $tableRow2->text('row8', 1)->rules('required');;

        $tableRow3 = new TableRow();
        $tableRow3->text('row9', 1);
        $tableRow3->text('row10', 2)->rules('required');;
        $tableRow3->text('row11', 1);

        $form->table('Using rowspan')
            ->setRows([$tableRow, $tableRow1, $tableRow3, $tableRow2]);

        $form->divide();
        /*************************************/


        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));

        return $form;
    }
```
