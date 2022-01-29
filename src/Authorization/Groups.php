<?php

namespace Sparks\Shield\Authorization;

use RuntimeException;
use Sparks\Shield\Entities\Group;

/**
 * Provides utility feature for working with
 * groups, adding permissions, etc.
 */
class Groups
{
    public function __construct()
    {
        if (! function_exists('setting')) {
            helper('setting');
        }
    }

    /**
     * Grabs a group info from settings.
     *
     * @return Group|null
     */
    public function info(string $group)
    {
        $info = setting('AuthGroups.groups')[strtolower($group)] ?? null;

        if (empty($info)) {
            return null;
        }

        $info['alias'] = $group;

        return new Group($info);
    }

    /**
     * Saves or creates the group.
     */
    public function save(Group $group)
    {
        if (empty($group->title)) {
            throw new RuntimeException(lang('Auth.missingTitle'));
        }

        $groups = setting('AuthGroups.groups');

        $alias = $group->alias;

        if (empty($alias)) {
            $alias = strtolower(url_title($group->title));
        }

        $groups[$alias] = [
            'title'       => $group->title,
            'description' => $group->description,
        ];

        // Save it
        setting('AuthGroups.groups', $groups);
    }
}
