<?php

namespace CodeIgniter\Shield\Authorization\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table          = 'auth_permissions_users';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields  = [
        'user_id',
        'permission',
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
        $permission = $this->builder()
            ->select('permission')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        return array_column($permission, 'permission');
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
            ->whereNotIn('permission', $cache)
            ->delete();
    }
}
