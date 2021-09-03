<?php
namespace App\Services\Report\Query;

use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Routing\RouterInterface;

use App\Services\Report\Query\MfwQuery;

class MfwButton extends MfwQuery
{
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function queryField($form, $element, $name)
    {
        $options = isset($element['options']) ? $element['options'] : [];
        if (isset($element['url'])) {
            $options['attr'] = isset($options['attr']) ? $options['attr'] : [];
            $options['attr']['data-mfw_href'] = $this->router->generate(
                $element['url']['href'],
                isset($element['url']['params']) ? $element['url']['params'] : []
            );
        }
        $form->add($name, ButtonType::class, $options);
    }

    public function inputField($form, $element, $name)
    {
        $form->add($name, ButtonType::class);
    }
}
