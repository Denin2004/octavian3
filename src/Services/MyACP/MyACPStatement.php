<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace App\Services\MyACP;

use Doctrine\DBAL\Driver\OCI8\OCI8Statement;
use App\Services\MyACP\MyACPConnection;

class MyACPStatement extends OCI8Statement
{
    private $errBind = [];
    private $statement;
    private $lastError = '';

    public function __construct($dbh, $statement, MyACPConnection $conn)
    {
        parent::__construct($dbh, $statement, $conn);
        $this->statement = $statement;
    }

    public function execSQL($params)
    {
        $params['cursors'] = isset($params['cursors']) ? $params['cursors'] : [];
        $params['in'] = isset($params['in']) ? $params['in'] : [];
        $params['out'] = isset($params['out']) ? $params['out'] : [];
        $params['lobs'] = isset($params['lobs']) ? $params['lobs'] : [];
        $params['collections'] = isset($params['collections']) ? $params['collections'] : [];
        $cursors = [];
        foreach ($params['cursors'] as $cursor) {
            $cursors[] = oci_new_cursor($this->_dbh);
            $this->boundValues[$cursor] = &$cursors[count($cursors)-1];
            oci_bind_by_name($this->_sth, $cursor, $cursors[count($cursors)-1], -1, OCI_B_CURSOR);
        }
        $res = [];
        $in_out = [];
        foreach ($params['out'] as $out) {
            $res[$out] = '';
            if (array_key_exists($out, $params['in'])) {
                $res[$out] = $params['in'][$out];
                $in_out[$out] = $params['in'][$out];
                unset($params['in'][$out]);
            }
            oci_bind_by_name($this->_sth, $out, $res[$out], 1000);
        }
        $this->buildLOB($params['lobs']);

        $collections = [];
        foreach ($params['collections'] as $collection) {
            $stream = oci_new_collection($this->_dbh, $collection['type'], $collection['schema']);
            $collections[] = $stream;
            foreach ($collection['values'] as $value) {
                $stream->append($value);
            }
            oci_bind_by_name($this->_sth, $collection['column'], $stream, -1, OCI_B_NTY);
        }
        $isAutoCommit = ($this->_conn->getExecuteMode() == OCI_COMMIT_ON_SUCCESS) && (count($params['lobs']) != 0);
        if ($isAutoCommit) {
            $this->_conn->beginTransaction();
        }
        try {
            $resExec = $this->execute($params['in']);
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            $this->freeLOB($params['lobs']);
            if ($isAutoCommit) {
                $this->_conn->rollBack();
            }
            oci_free_statement($this->_sth);
            foreach ($collections as $collection) {
                $collection->free();
            }
            $this->collection = [];
            return $res;
        }
        if ((!$resExec)&&(count($this->errBind) != 0)) {
            foreach ($this->errBind as $key) {
                unset($params['in'][$key]);
            }
            $this->errBind = [];
            $this->_sth = oci_parse($this->_dbh, $this->statement);
            foreach ($params['cursors'] as $cursor) {
                $cursors[] = oci_new_cursor($this->_dbh);
                $this->boundValues[$cursor] = &$cursors[count($cursors)-1];
                oci_bind_by_name($this->_sth, $cursor, $cursors[count($cursors)-1], -1, OCI_B_CURSOR);
            }
            foreach ($params['out'] as $out) {
                $res[$out] = $in_out[$out] ? $in_out[$out] : '';
                oci_bind_by_name($this->_sth, $out, $res[$out], 1000);
            }
            foreach ($params['collections'] as $key => $collection) {
                oci_bind_by_name($this->_sth, $collection['column'], $collections[$key], -1, OCI_B_NTY);
            }
            try {
                $this->execute($in_params);
            } catch (\Exception $e) {
                $this->lastError = $e->getMessage();
                $this->freeLOB($lob_params);
                if ($isAutoCommit) {
                    $this->_conn->rollBack();
                }
                oci_free_statement($this->_sth);
                foreach ($collections as $collection) {
                    $collection->free();
                }
                $this->collection = [];
                return $res;
            }
        }
        $this->freeLOB($params['lobs']);
        foreach ($cursors as $key => $cursor) {
            oci_execute($cursor);
            $res[$params['cursors'][$key]] = [];
            oci_fetch_all($cursor, $res[$params['cursors'][$key]], 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
            oci_free_statement($cursor);
        }
        if ($isAutoCommit) {
            $this->_conn->commit();
        }
        if ($this->_conn->getExecuteMode() != OCI_NO_AUTO_COMMIT) {
            oci_free_statement($this->_sth);
        }
        foreach ($collections as $collection) {
            $collection->free();
        }
        foreach ($params['lobs'] as $lob) {
            if (isset($lob['out'])) {
                $res[$lob['name']] = $lob['data'];
                unset($lob['data']);
            }
        }
        return (count($res) == 1 and count($cursors) == 1) ? $res[$params['cursors'][0]] : $res;
    }

    private function buildLOB(&$lob_params)
    {
        foreach ($lob_params as $key => $lob) {
            switch ($lob['type']) {
                case OCI_B_CLOB:
                    $lob_params[$key]['descriptor'] = oci_new_descriptor($this->_dbh, OCI_D_LOB);
                    oci_bind_by_name($this->_sth, $lob['name'], $lob_params[$key]['descriptor'], -1, OCI_B_CLOB);
                    break;
                case OCI_B_BLOB:
                    //$lob_params[$key]['descriptor'] = oci_new_descriptor($this->_dbh, OCI_DTYPE_FILE);
                    $lob_params[$key]['descriptor'] = oci_new_descriptor($this->_dbh, OCI_D_LOB);
                    oci_bind_by_name($this->_sth, $lob['name'], $lob_params[$key]['descriptor'], -1, OCI_B_BLOB);
                    break;
                case OCI_B_ROWID:
                    $lob_params[$key]['descriptor'] = oci_new_descriptor($this->_dbh, OCI_D_ROWID);
                    oci_bind_by_name($this->_sth, $lob['name'], $lob_params[$key]['descriptor'], -1, OCI_B_ROWID);
                    break;
            }
        }
    }

    private function freeLOB(&$lob_descriptors)
    {
        foreach ($lob_descriptors as $key => $lob) {
            if ($this->lastError == '') {
                switch ($lob['type']) {
                    case OCI_B_BLOB:
                        if (isset($lob['data'])) {
                            if (!$lob['descriptor']->save($lob['data'])) {
                                $this->lastError = 'oracle_error.write_lob';
                                return false;
                            };
                        } else {
                            $lob_descriptors[$key]['data'] = $lob['descriptor'] ? $lob['descriptor']->load() : null;
                            $lob_descriptors[$key]['out'] = true;
                        }
                        break;
                    case OCI_B_CLOB:
                        if (isset($lob['data'])) {
                            if (!$lob['descriptor']->save($lob['data'])) {
                                $this->lastError = 'oracle_error.write_lob';
                                return false;
                            };
                        } else {
                            $lob_descriptors[$key]['data'] = $lob['descriptor'] ? $lob['descriptor']->load() : null;
                            $lob_descriptors[$key]['out'] = true;
                        }
                        break;
                }
            }
            if ($lob_descriptors[$key]['descriptor']) {
                $lob_descriptors[$key]['descriptor']->free();
            }
        }
        return true;
    }

    public function bindParam($column, &$variable, $type = null, $length = null)
    {
        $column = isset($this->_paramMap[$column]) ? $this->_paramMap[$column] : $column;

        if ($type == \PDO::PARAM_LOB) {
            $lob = oci_new_descriptor($this->_dbh, OCI_D_LOB);
            $lob->writeTemporary($variable, OCI_TEMP_BLOB);

            $this->boundValues[$column] =& $lob;

            return oci_bind_by_name($this->_sth, $column, $lob, -1, OCI_B_BLOB);
        } elseif ($length !== null) {
            $this->boundValues[$column] =& $variable;

            return oci_bind_by_name($this->_sth, $column, $variable, $length);
        }
        try {
            oci_bind_by_name($this->_sth, $column, $variable);
        } catch (\Exception $e) {
            $this->errBind[] = $column;
            return false;
        }
        $this->boundValues[$column] =& $variable;
        return true;
    }

    public function bindArray($column, $values, $type, $schema)
    {
        $collection = oci_new_collection($this->_dbh, $type, $schema);
        $this->collection[] = [
            'collection' => $collection,
            'column' => $column
        ];
        foreach ($values as $value) {
            $collection->append($value);
        }
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

    public function getLastError()
    {
        return $this->lastError;
    }

    public function free() : void
    {
        oci_free_statement($this->_sth);
    }

    public function bindCursor($name)
    {
        $cursor = oci_new_cursor($this->_dbh);
        oci_bind_by_name($this->_sth, $name, $cursor, -1, OCI_B_CURSOR);
        $this->boundValues[$name] = &$cursor;
    }

    public function getCursor($name)
    {
        $resCur = [];
        oci_execute($this->boundValues[$name]);
        oci_fetch_all($this->boundValues[$name], $resCur, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
        oci_free_statement($this->boundValues[$name]);
        return $resCur;
    }
}
