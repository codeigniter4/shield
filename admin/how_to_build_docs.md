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
```

## Build the Docs

```consolse
mkdocs build
```

## See the Docs

```consolse
mkdocs serve
```

## Deploy Manually

The documentation is built and deployed automatically by GitHub Actions. But if
you need to deploy manually, run the following command:

```console
mkdocs gh-deploy
```
