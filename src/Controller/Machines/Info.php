<?php
namespace App\Controller\Machines;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

//use App\Services\Socks\Socks;
use App\Entity\Machines;

class Info extends AbstractController
{
    public function info(Machines $machines, $id)
    {
        $info = $machines->status($id);
        if (isset($info['error'])) {
            return new JsonResponse([
                'success' => false,
                'error' => $info['error']
            ]);
        }
        $versions = explode('; ', $info['status'][0]['UCB_VERSION']);
        $info['status'][0]['PTM_VERSION'] = '';
        if (count($versions) > 1) {
            $info['status'][0]['PTM_VERSION'] = $versions[1];
        }
        $info['status'][0]['UCB_VERSION'] = $versions[0];
            return new JsonResponse([
                'success' => true,
                'data' => [
                    'machineInfo' => $info
                ]
            ]);
    }
}
