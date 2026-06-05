# How to Build Shield Documentation

We use Material for MkDocs for our documentation.
See https://squidfunk.github.io/mkdocs-material/getting-started/.

## Requirements

- Python3
- pip

## Installation

```console
pip3 install mkdocs
pip3 install mkdocs-material
pip3 install mkdocs-git-revision-date-localized-plugin
pip3 install mkdocs-redirects
pip3 install mike
```

## Build the Docs

```console
mkdocs build
```

## See the Docs

```console
mkdocs serve
```

To preview the versioned site (with the version selector), use `mike` instead:

```console
mike serve
```

## Versioning

The published documentation is versioned with
[mike](https://github.com/jimporter/mike). Each version lives in its own
subdirectory on the `gh-pages` branch:

- `dev` — the latest `develop` branch.
- `X.Y` — a published stable release (e.g. `1.5`), with the `latest` alias
  pointing at the most recent one. `latest` is also the default version users
  land on.

## Deploy Manually

The documentation is built and deployed automatically by GitHub Actions:
pushes to `develop` publish the `dev` version, and pushing a `vX.Y.Z` tag
publishes the matching `X.Y` version and updates `latest`.

> **Warning:** Do **not** run `mkdocs gh-deploy`. It overwrites the root of the
> `gh-pages` branch and would destroy the versioned structure managed by mike.

If you need to deploy manually, use `mike`. For example, to publish a stable
release and point `latest` at it:

```console
mike deploy --push --update-aliases X.Y latest
mike set-default --push latest
```

Or to (re)publish the development docs:

```console
mike deploy --push dev
```
