<?php

namespace CodeIgniter\Shield\Authorization\Models;

use CodeIgniter\Model;

class GroupModel extends Model
{
    protected $table          = 'auth_groups_users';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields  = [
        'user_id',
        'group',
        'created_at',
    ];
    protected $useTimestamps      = false;
    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * @param int|string $userId
     */
    public function getByUserId($userId): array
    {
        $groups = $this->builder()
            ->select('group')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        return array_column($groups, 'group');
    }

    /**
     * @param int|string $userId
     */
    public function deleteAll($userId): void
    {
        $this->builder()
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * @param int|string $userId
     * @param mixed      $cache
     */
    public function deleteNotIn($userId, $cache): void
    {
        $this->builder()
            ->where('user_id', $userId)
            ->whereNotIn('group', $cache)
            ->delete();
    }
}
