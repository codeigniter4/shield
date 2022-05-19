<?php

namespace CodeIgniter\Shield\Models;

use CodeIgniter\Shield\Exceptions\RuntimeException;

trait CheckQueryReturnTrait
{
    private function checkQueryReturn(bool $return): void
    {
        if ($return === false) {
            $error   = $this->db->error();
            $message = 'Query error: ' . $error['code'] . ', '
                . $error['message'] . ', query: ' . $this->db->getLastQuery();

            throw new RuntimeException($message, $error['code']);
        }
    }
}
