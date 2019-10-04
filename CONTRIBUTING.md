# Contributing

To get started follow these steps:

1. `git clone git@github.com:humanmade/gaussholder.git` or fork the repository and clone your fork.
1. `cd gaussholder`
1. `npm install`
1. `npm run build` (to build the assets)

You should then start working on your fork or branch.

## Making a pull request

When you make a pull request it will be reviewed. You should also update the `CHANGELOG.md` - add lines after the title such as `- Bug: Fixed a typo #33` to describe each change and link to the issues if applicable.

## Making a new release

1. Create a new branch
2. Update the version number in the comment header of `plugin.php` to reflect the nature of the changes, this plugin follows semver versioning.
  - For small changes like bug fixes update the patch version
  - For changes that add functionality without changing existing functionality update the minor version
  - For breaking or highly significant changes update the major version
3. Add a title heading for the version number above the latest updates in `CHANGELOG.md`
4. Create a pull request for the branch
5. Once merged a release will be built and deployed by CircleCI corresponding to the version number in `plugin.php`
