<?php

namespace Ichynul\RowTable\Field;

use Encore\Admin\Form\Field;

class Show extends Field
{
    /**
     * @var string
     */
    protected $text = '';

    /**
     * @var string
     */
    protected $textWidth = 'auto';

    /**
     * text-align
     *
     * @var string
     */
    protected $align = 'center';

    /**
     * set text-align
     *
     * @param [strign] $align
     * @return $this
     */
    public function Textalign($align)
    {
        $this->align = $align;

        return $this;
    }

    /**
     * set text-align
     *
     * @param [strign] $align
     * @return $this
     */
    public function textWidth($width)
    {
        $this->textWidth = $width;

        return $this;
    }

    /**
     * Create a new Show instance.
     *
     * @param mixed $text
     * @param array $arguments
     */
    public function __construct($text, $arguments = [])
    {
        $this->text = $text;

        $this->width['field']  = array_get($arguments, 0, 12);
    }

    public function render()
    {
        $viewClass = [
            'label'      => "col-sm-{$this->width['field']} {$this->getLabelClass()}",
            'form-group' => 'form-group ',
        ];

        return <<<EOT
<div class="form-group" style="text-align:{$this->align};width:{$this->textWidth};min-width:100px;">
    <label style="text-align:{$this->align};" class="{$viewClass['label']} control-label">{$this->text}</label>
</div>
EOT;
    }
}
