<?php
namespace App\Services\SiteConfig;

class SiteConfig
{
    protected $config = [];

    public function __construct($projectDir)
    {
        $this->config = json_decode(file_get_contents($projectDir.'/templates/site_config.json.twig'), true);
        $decimals = $this->config['numeral']['decimals'] != 0 ? '.'.str_repeat('0', $this->config['numeral']['decimals']) : '';
        if ($this->config['numeral']['thousandSeparator'] == '') {
            $this->config['xls_number_format'] = '0';
            $this->config['xls_int_format'] = '0';
        } else {
            $this->config['xls_number_format'] = '#,##0';
            $this->config['xls_int_format'] = '#,##0';
        }
        $this->config['xls_number_format'].= $decimals;
    }

    public function get($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : '';
    }
}
