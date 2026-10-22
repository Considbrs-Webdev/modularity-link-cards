---
name: dependency-update
description: Update project dependencies in small, verified steps - check what is outdated, read changelogs for breaking changes, update, test, and report. Use when asked to "update dependencies", "upgrade packages" or handle security advisories.
---

# Dependency update

Hard rules: update in small groups, run the tests after each, never leave the project broken,
and confirm before major-version upgrades or anything that changes the runtime version.

## 1. Baseline

Run install and the full test and build commands first. If they already fail, stop and
report; you cannot judge an update against a broken baseline. Work on a branch, not the
release branch.

## 2. Survey

List outdated and vulnerable packages with the ecosystem's tools (`npm outdated`,
`npm audit`, `composer outdated`, `composer audit`, `pip list --outdated`, etc.). Classify as
patch, minor, major. Note direct vs transitive and dev vs production.

## 3. Plan and confirm

Propose an order: security fixes and patches first, then minors, then majors one at a time.
For each major, read the changelog or upgrade guide and summarize breaking changes affecting
this codebase (search the code for the APIs involved). Ask before doing any major upgrade.

## 4. Update

Per group: update the manifest and lock file with the package manager (never hand-edit lock
files), install, run lint, tests and build. If something breaks, fix it when the fix is
small and clear; otherwise revert that package and note why. One commit per group with a
clear message.

## 5. Report

List what was updated, what was skipped and why, remaining advisories, and anything that
needs manual testing in a browser or real environment. Do not merge or release.
