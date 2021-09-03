<?php
namespace App\Services\Security;

use Twig\Environment;

use App\Entity\Users;
use App\Entity\WebRoles;

class Access
{
    protected $twig;
    protected $usersDB;
    protected $rolesDB;

    public function __construct(Environment $twig, Users $usersDB, WebRoles $rolesDB)
    {
        $this->twig = $twig;
        $this->usersDB =$usersDB;
        $this->rolesDB = $rolesDB;
    }

    public function user($id)
    {
        $defaultAccess = json_decode($this->twig->render('/uac/uac.json.twig', ['reports' => []]), true);
        $userAccess = $this->usersDB->userAccess($id);
        $userAccess['widgets'] = [];
        foreach ($userAccess['rolesAccess'] as $roleAccess) {
            $access = array_replace_recursive(
                $defaultAccess,
                $roleAccess
            );
            $this->getRoutes($userAccess['routes'], $access['routes']);
            foreach ($roleAccess['widgets'] as $widget) {
                if (!in_array($widget, $userAccess['widgets'])) {
                    $userAccess['widgets'][] = $widget;
                }
            }
        }
        $userAccess['routes'] = array_unique($userAccess['routes']);
        unset($userAccess['rolesAccess']);
        return $userAccess;
    }

    public function role($id)
    {
        $dbAccess = $this->roleDB->access($id);
        $access = json_decode(
            $this->twig->render(
                '/uac/uac.json.twig',
                [
                    'reports' => $dbAccess['result'],
                    'role_id' => $id
                ]
            ),
            true
        );
        if (($dbAccess['config'] != '')and($dbAccess['config'] != null)) {
            $access = array_replace_recursive(
                $access,
                json_decode($dbAccess['config'], true)
            );
        }
        $dbAccess['routes'] = [];
        foreach ($access['routes'] as $key => $item) {
            $dbAccess['routes'][] = $this->toJsTree($item, $key);
        }
        foreach ($dbAccess['routes'] as $k => $child) {
            if (!$child) {
                unset($dbAccess['routes'][$k]);
            }
        }
        foreach ($access as $key => $item) {
            if ($key != 'routes') {
                $dbAccess[$key] = $item;
            }
        }
        unset($dbAccess['config']);
        return $dbAccess;
    }

    public function rolePost($data)
    {
        $routes = json_decode($data['routes'], true);
        $config = [
            'routes' => [],
            'widgets' => json_decode($data['widgets'], true),
            'version' => 1
        ];
        $data['reports'] = [];
        $this->removeSubReports($routes);
        foreach ($routes as $item) {
            if ($item['data']['key'] == 'report.all') {
                foreach ($item['children'] as $report) {
                    if ($report['state']['selected'] == true) {
                        $data['reports'][] = $report['data']['routes'][0];
                    }
                }
            } else {
                $config['routes'][$item['data']['key']] = $this->toConfig($item);
            }
        }
        return $this->roleDB->accessPost([
            'id' => $data['id'],
            'name' => $data['name'],
            'reports' => $data['reports'],
            'config' => json_encode($config)
        ]);
    }

    protected function removeSubReports(&$config)
    {
        foreach ($config as $key => $item) {
            if (isset($item['type'])&&($item['type'] == 'subReports')) {
                unset($config[$key]);
            } else {
                if (isset($item['children'])) {
                    $this->removeSubReports($config[$key]['children']);
                }
            }
        }
    }

    protected function toJsTree($item, $key)
    {
        if (isset($item['routes'])) {
            return array_merge([
                'text' => $key,
                'state' => ['selected' => isset($item['access']) ? $item['access'] : false],
                'data' => [
                    'routes' => $item['routes'],
                    'access' => $item['access'],
                    'key' => $key
                ]
            ], isset($item['typeNode']) ? ['type' => $item['typeNode']] : []);
        }
        if (isset($item['access'])) {
            unset($item['access']);
        }
        $res = array_merge([
            'text' => $key,
            'children' => [],
            'state' => ['selected' => isset($item['access']) ? $item['access'] : false],
            'data' => [
                'key' => $key
            ]
        ], isset($item['typeNode']) ? ['type' => $item['typeNode']] : []);
        if (!is_array($item)) {
            return false;
        }
        foreach ($item as $k => $child) {
            $children = $this->toJsTree($child, $k);
            if ($children !== false) {
                $res['children'][] = $children;
            }
        }
        foreach ($res['children'] as $k => $child) {
            if (!$child) {
                unset($res['children'][$k]);
            }
        }
        if (count($res['children']) == 0) {
            return false;
        }
        return $res;
    }

    protected function toConfig($item)
    {
        $res = [];
        if (isset($item['data']['routes'])) {
            return [
                'access' => $item['state']['selected']
            ];
        }
        if (isset($item['children'])) {
            foreach ($item['children'] as $child) {
                $res[$child['data']['key']] = $this->toConfig($child);
            }
        }
        return $res;
    }

    protected function getRoutes(&$access, $item)
    {
        if (isset($item['routes'])) {
            if ($item['access'] == true) {
                foreach ($item['routes'] as $route) {
                    $access[] = $route;
                }
            }
        } else {
            foreach ($item as $k => $child) {
                if ($k != 'access') {
                    $this->getRoutes($access, $child);
                }
            }
        }
    }
}
