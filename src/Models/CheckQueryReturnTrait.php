<?php

namespace CodeIgniter\Shield\Models;


trait CheckQueryReturnTrait
{
    private function checkQueryReturn(bool $return): void
    {
        if ($return === false) {
            $error   = $this->db->error();
            $message = 'Query error: ' . $error['code'] . ', '
                . $error['message'] . ', query: ' . $this->db->getLastQuery();

            throw new DatabaseException($message, $error['code']);
        }
    }
}
