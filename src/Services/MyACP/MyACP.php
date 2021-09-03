<?php
namespace App\Services\MyACP;

use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;

use App\Services\MyACP\MyACPEntity;
use App\Services\SiteConfig\SiteConfig;

class MyACP
{

    private $config;
    private $db_date_format = '';
    private $loginUser = null;
    private $lang;
    private $langFlag;
    private $lastError = '';
    private $session;
    private $connection;
    private $siteConfig;

    public function __construct($config, Session $session, SiteConfig $siteConfig)
    {
        $this->session = $session;
        $this->config = $config;
        $this->db_date_format = $siteConfig->get('db_date_format');
        $this->siteConfig = $siteConfig;
        $userName = $session->get('user_name') ? $session->get('user_name') : '';
        if ($userName != '') {
            $this->setConnect($userName);
        } else {
            $this->setConnect('', true);
        }
        $this->lang = filter_input(INPUT_COOKIE, 'lang') ? filter_input(INPUT_COOKIE, 'lang') :
            filter_input(INPUT_GET, 'lang') ? filter_input(INPUT_GET, 'lang') : 'en';
        $this->langFlag = $this->lang == 'en' ? 1 : 0;
    }

    public function setUser($user)
    {
        $this->loginUser = $user;
    }

    public function execSQL($params)
    {
        $res = [];
        $this->lastError = '';
        try {
            $res = $this->connection->execSQL($params);
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
        }
        $this->lastError = $this->lastError == '' ? $this->connection->getLastError() : $this->lastError;
        return $res;
    }

    public function getNLSDateFormat()
    {
        return $this->nlsFormat;
    }

    public function getRepository($name) : MyACPEntity
    {
        $modelName = 'App\\Entity\\'.$name;
        return new $modelName($this, $this->siteConfig);
    }

    public function tryConnect($user, $password)
    {
        $config = new Configuration();
        $oldUser = $this->config['user'];
        $oldPassword = $this->config['password'];
        $this->config['user'] = $user;
        $this->config['password'] = $password;
        $conn = DriverManager::getConnection($this->config, $config);
        $status = '0';
        try {
            $this->connection = $conn->getWrappedConnection();
        } catch (\Exception $e) {
            $status = 'ORA-'.sprintf('%05d', $e->getErrorCode());
        }
        switch ($status) {
            case 'ORA-28001':
                return 'psw_expired';
            case 'ORA-24960':
            case 'ORA-01017':
            case 'ORA-01005':
            case 'ORA-28000':
                return 'oracle_error.'.$status;
            case '-1':
                return 'errors.unknown_db_error';
            case '0':
                $this->connection = null;
                $this->session->set('user_name', $user);
                $this->config['user'] = $oldUser;
                $this->config['password'] = $oldPassword;
                $this->setConnect($user);
                return $this->lastError == '' ? 'Ok' : 'oracle_error.'.$this->lastError;
            default:
                return 'errors.unknown_error';
        }
    }

    public function getDBDateFormat()
    {
        return $this->db_date_format;
    }

    public function getUser()
    {
        return $this->loginUser;
    }

    public function getSLangFlag()
    {
        return $this->langFlag;
    }

    public function getSLang()
    {
        return $this->lang;
    }

    public function getStatement($sql)
    {
        return $this->connection->getStatement($sql);
    }

    public function getError($addCode = '', $text = false, $line = 0)
    {
        if ($this->lastError == '') {
            return '';
        }
        $res = explode(':', explode("\n", $this->lastError)[$line]);
        if ($text == true) {
            array_shift($res);
            return ltrim(implode('', $res));
        }
        return 'oracle_error.'.$res[0].$addCode;
    }

    private function setConnect($user, $sa = false)
    {
        if ($this->connection) {
            return;
        }
        $config = new Configuration();
        $oldUser = $this->config['user'];
        $this->config['user'] = $this->config['user'].($sa ==  true ? '' : '['.$user.']');
        $conn = DriverManager::getConnection($this->config, $config);
        $status = '0';
        try {
            $this->connection = $conn->getWrappedConnection();
        } catch (\Exception $e) {
            $status = 'ORA-'.sprintf('%05d', $e->getErrorCode());
        }
        $this->config['user'] = $oldUser;
        switch ($status) {
            case 'ORA-01017':
            case 'ORA-01005':
            case 'ORA-28000':
                $this->lastError = $status;
                return;
            case '-1':
                $this->lastError = 'unknown_db_error';
                return;
            case '0':
                $this->lastError = '';
                $this->connection->execSQL([
                    'sql' => 'ALTER SESSION SET NLS_DATE_FORMAT=\''.$this->db_date_format.'\''
                ]);
                $this->connection->execSQL([
                    'sql' =>'ALTER SESSION SET NLS_TIMESTAMP_FORMAT=\''.$this->db_date_format.'\''
                ]);
                break;
            default:
                $this->lastError = 'unknown_error';
                return;
        }
    }

    public function connectMaintenance()
    {
        if ($this->connection) {
            return;
        }
        $config = new Configuration();
        $oldUser = $this->config['user'];
        $this->config['sessionMode'] = OCI_SYSDBA;
        $this->config['user'] = 'sys';
        $this->config['password'] = $this->config['maintenance'];
        $conn = DriverManager::getConnection($this->config, $config);
        $status = '0';
        try {
            $this->connection = $conn->getWrappedConnection();
        } catch (\Exception $e) {
            $status = 'ORA-'.sprintf('%05d', $e->getErrorCode());
        }
        $this->config['user'] = $oldUser;
        switch ($status) {
            case 'ORA-01017':
            case 'ORA-01005':
            case 'ORA-28000':
                $this->lastError = $status;
                return;
            case '-1':
                $this->lastError = 'unknown_db_error';
                return;
            case '0':
                $this->lastError = '';
                $this->connection->execSQL([
                    'sql' => 'ALTER SESSION SET NLS_DATE_FORMAT=\''.$this->db_date_format.'\''
                ]);
                $this->connection->execSQL([
                    'sql' =>'ALTER SESSION SET NLS_TIMESTAMP_FORMAT=\''.$this->db_date_format.'\''
                ]);
                break;
            default:
                $this->lastError = 'unknown_error';
                return;
        }
    }

    public function setConnectSA()
    {
        $this->setConnect('', true);
    }

    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    public function rollBack()
    {
        $this->connection->rollBack();
    }

    public function commit()
    {
        $this->connection->commit();
    }

    public function getLastError()
    {
        return $this->lastError;
    }
}
