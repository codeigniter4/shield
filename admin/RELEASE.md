# Release Process

> Documentation guide based on the releases of `1.0.0-beta.5` on March 17, 2023.
>
> -kenjis

## Changelog

When generating the changelog each Pull Request to be included must have one of
the following [labels](https://github.com/codeigniter4/shield/labels):
- **bug** ... PRs that fix bugs
- **enhancement** ... PRs to improve existing functionalities
- **new feature** ... PRs for new features
- **refactor** ... PRs to refactor
- **lang** ... PRs for new/update language

PRs with breaking changes must have the following additional label:
- **breaking change** ... PRs that may break existing functionalities

### Check Generated Changelog

This process is checking only. Do not create a release.

To auto-generate, navigate to the
[Releases](https://github.com/codeigniter4/shield/releases) page,
click the "Draft a new release" button.

* Tag: "v1.0.0-beta.5" (Create new tag)
* Target: develop

Click the "Generate release notes" button.

Check the resulting content. If there are items in the *Others* section which
should be included in the changelog, add a label to the PR and regenerate
the changelog.

## Preparation

* Clone **codeigniter4/shield** and resolve any necessary PRs
    ```console
    git clone git@github.com:codeigniter4/shield.git
    ```
* Merge any Security Advisory PRs in private forks

## Process

> **Note** Most changes that need noting in the User Guide and docs should have
> been included with their PR, so this process assumes you will not be
> generating much new content.

* Create a new branch `release-1.x.x`
* Update **src/Auth.php** with the new version number:
  `const SHIELD_VERSION = '1.x.x';`
* Commit the changes with "Prep for 1.x.x release" and push to origin
* Create a new PR from `release-1.x.x` to `develop`:
    * Title: "Prep for 1.x.x release"
    * Description: "Updates version references for `1.x.x`." (plus checklist)
* Let all tests run, then review and merge the PR
* Create a new PR from `develop` to `master`:
    * Title: "1.x.x Ready code"
    * Description: blank
* Merge the PR
* Create a new Release:
    * Version: "v1.x.x"
    * Target: master
    * Title: "v1.x.x"
    * Click the "Generate release notes" button
    * Remove "### Others (Only for checking. Remove this category)" section
    * Check "Create a discussion for this release"
    * Click the "Publish release" button
* Watch for the "docs" action and verify that the user guide updated:
    * [docs](https://github.com/codeigniter4/shield/actions/workflows/docs.yml)
* Fast-forward `develop` branch to catch the merge commit from `master`
    ```console
    git fetch origin
    git checkout develop
    git merge origin/develop
    git merge origin/master
    git push origin HEAD # @TODO can't push to protected branch.
    ```
* Publish any Security Advisories that were resolved from private forks
* Announce the release on the forums and Slack channel
  (note: this forum is restricted to administrators):
    * Make a new topic in the "News & Discussion" forums:
      https://forum.codeigniter.com/forum-2.html
    * The content is somewhat organic, but should include any major features and
      changes as well as a link to the User Guide's changelog
