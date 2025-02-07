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

namespace CodeIgniter\Shield\Collectors;

use CodeIgniter\Debug\Toolbar\Collectors\BaseCollector;
use CodeIgniter\Shield\Auth as ShieldAuth;

/**
 * Debug Toolbar Collector for Auth
 */
class Auth extends BaseCollector
{
    /**
     * Whether this collector has data that can
     * be displayed in the Timeline.
     *
     * @var bool
     */
    protected $hasTimeline = false;

    /**
     * Whether this collector needs to display
     * content in a tab or not.
     *
     * @var bool
     */
    protected $hasTabContent = true;

    /**
     * Whether this collector has data that
     * should be shown in the Vars tab.
     *
     * @var bool
     */
    protected $hasVarData = false;

    /**
     * The 'title' of this Collector.
     * Used to name things in the toolbar HTML.
     *
     * @var string
     */
    protected $title = 'Auth';

    private ShieldAuth $auth;

    public function __construct()
    {
        $this->auth = service('auth');
    }

    /**
     * Returns any information that should be shown next to the title.
     */
    public function getTitleDetails(): string
    {
        return ShieldAuth::SHIELD_VERSION . ' | ' . get_class($this->auth->getAuthenticator());
    }

    /**
     * Returns the data of this collector to be formatted in the toolbar
     */
    public function display(): string
    {
        if ($this->auth->loggedIn()) {
            $user        = $this->auth->user();
            $groups      = $user->getGroups();
            $permissions = $user->getPermissions();

            $groupsForUser      = implode(', ', $groups);
            $permissionsForUser = implode(', ', $permissions);

            $html = '<h3>Current User</h3>';
            $html .= '<table><tbody>';
            $html .= "<tr><td style='width:150px;'>User ID</td><td>#{$user->id}</td></tr>";
            $html .= "<tr><td>Username</td><td>{$user->username}</td></tr>";
            $html .= "<tr><td>Email</td><td>{$user->email}</td></tr>";
            $html .= "<tr><td>Groups</td><td>{$groupsForUser}</td></tr>";
            $html .= "<tr><td>Permissions</td><td>{$permissionsForUser}</td></tr>";
            $html .= '</tbody></table>';
        } else {
            $html = '<p>Not logged in.</p>';
        }

        return $html;
    }

    /**
     * Gets the "badge" value for the button.
     *
     * @return int|string|null ID of the current User, or null when not logged in
     */
    public function getBadgeValue()
    {
        return $this->auth->loggedIn() ? $this->auth->id() : null;
    }

    /**
     * Display the icon.
     *
     * Icon from https://icons8.com - 1em package
     */
    public function icon(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAADLSURBVEhL5ZRLCsIwGAa7UkE9gd5HUfEoekxxJx7AhXoCca/fhESkJiQxBHwMDG3S/9EmJc0n0JMruZVXK/fMdWQRY7mXt4A7OZJvwZu74hRayIEc2nv3jGtXZrOWrnifiRY0OkhiWK5sWGeS52bkZymJ2ZhRJmwmySxLCL6CmIsZZUIixkiNezCRR+kSUyWH3Cgn6SuQIk2iuOBckvN+t8FMnq1TJloUN3jefN9mhvJeCAVWb8CyUDj0vxc3iPFHDaofFdUPu2+iae7nYJMCY/1bpAAAAABJRU5ErkJggg==';
    }
}
