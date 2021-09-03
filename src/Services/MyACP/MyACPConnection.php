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

use Doctrine\DBAL\Driver\OCI8\OCI8Connection;

/**
 * OCI8 implementation of the Connection interface.
 *
 * @since 2.0
 */
class MyACPConnection extends OCI8Connection
{
    private $lastError = '';

    public function execSQL($params)
    {
        if (!isset($params['sql'])) {
            $this->lastError = 'No SQL';
            return null;
        }
        $stmt = new MyACPStatement($this->dbh, $params['sql'], $this);
        $res = $stmt->execSQL($params);
        $this->lastError = $stmt->getLastError();
        return $res;
    }

    public function getStatement($sql)
    {
        return new MyACPStatement($this->dbh, $sql, $this);
    }

    public function getLastError()
    {
        return $this->lastError;
    }
}
