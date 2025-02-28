<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Shield\Models;

use CodeIgniter\Shield\Entities\User;

class PermissionModel extends BaseModel
{
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

    protected function initialize(): void
    {
        parent::initialize();

        $this->table = $this->tables['permissions_users'];
    }

    public function getForUser(User $user): array
    {
        $rows = $this->builder()
            ->select('permission')
            ->where('user_id', $user->id)
            ->get()
            ->getResultArray();

        return array_column($rows, 'permission');
    }

    /**
     * @param int|string $userId
     */
    public function deleteAll($userId): void
    {
        $return = $this->builder()
            ->where('user_id', $userId)
            ->delete();

        $this->checkQueryReturn($return);
    }

    /**
     * @param int|string $userId
     */
    public function deleteNotIn($userId, mixed $cache): void
    {
        $return = $this->builder()
            ->where('user_id', $userId)
            ->whereNotIn('permission', $cache)
            ->delete();

        $this->checkQueryReturn($return);
    }

    /**
     * @param list<int>|list<string> $userIds
     *
     * @return array<int, array>
     */
    public function getPermissionsByUserIds(array $userIds): array
    {
        $permissions = $this->builder()
            ->select('user_id, permission')
            ->whereIn('user_id', $userIds)
            ->orderBy($this->primaryKey)
            ->get()
            ->getResultArray();

        return array_map(
            'array_keys',
            array_reduce($permissions, static function ($carry, $item) {
                $carry[$item['user_id']][$item['permission']] = true;

                return $carry;
            }, []),
        );
    }
}
