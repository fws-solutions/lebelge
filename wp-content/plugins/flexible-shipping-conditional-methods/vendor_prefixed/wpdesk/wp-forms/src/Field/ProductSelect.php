<?php

namespace FSConditionalMethodsVendor\WPDesk\Forms\Field;

class ProductSelect extends \FSConditionalMethodsVendor\WPDesk\Forms\Field\SelectField
{
    public function __construct()
    {
        parent::__construct();
        $this->set_multiple();
    }
    public function get_template_name()
    {
        return 'product-select';
    }
}
