<?php
namespace App\Services\RPN;

use App\Services\SiteConfig\SiteConfig;
use App\Services\RPN\RPN;

class RPNService
{
    protected $php_dec_point;
    protected $php_thousand_sep;

    public function __construct(SiteConfig $config)
    {
        $this->php_dec_point = $config->get('php_dec_point');
        $this->php_thousand_sep = $config->get('php_thousand_sep');
    }
    
    public function createRPN()
    {
        return new RPN($this->php_dec_point, $this->php_thousand_sep);
    }
}
