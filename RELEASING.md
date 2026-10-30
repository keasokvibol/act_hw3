# Release Management Guide

## 1. Purpose

This document describes the release management process for the IQA Office Management System (HW3).

The project uses GitHub for source code management, Git tags for release versioning, Jenkins for CI/CD automation, Docker for application containerization, Amazon ECR for image storage, and Docker Swarm for deployment.

## 2. Semantic Versioning Convention

The project follows Semantic Versioning:

`vMAJOR.MINOR.PATCH`

Examples:

- `v1.0.0` — First stable release.
- `v1.1.0` — New backward-compatible functionality.
- `v1.1.1` — Backward-compatible bug fix.
- `v2.0.0` — Release containing incompatible changes.

Version number rules:

- **MAJOR:** Increase when introducing incompatible changes.
- **MINOR:** Increase when adding backward-compatible functionality.
- **PATCH:** Increase when fixing backward-compatible bugs.

Every release must have a unique Git tag.

## 3. Release Preparation

Before creating a release:

1. Complete and test the application changes.
2. Commit the changes to Git.
3. Push the commits to the GitHub repository.
4. Confirm that the `main` branch contains the intended release changes.
5. Select the appropriate semantic version.

## 4. Creating a GitHub Release Tag

Create a release tag from the intended commit.

Example:

```bash
git switch main
git pull origin main
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

Pushing a Git release tag triggers the Jenkins release pipeline.

Ordinary branch pushes must not trigger the automatic release pipeline.

## 5. Docker Image Build and Storage

Jenkins builds the application Docker image using the Git release version as its image tag.

Example image tags:

- `v1.0.0`
- `v1.1.0`
- `v1.2.0`

Images are pushed to the dedicated Amazon ECR repository for HW3.

Each release image must retain its original version tag. Existing release tags must not be overwritten.

## 6. Deployment

The Jenkins pipeline supports the following actions:

- `build` — Build the Docker image for a specified version.
- `push` — Push the built image to Amazon ECR.
- `deploy` — Deploy an existing version to Docker Swarm.
- `rollback` — Redeploy a previously published version.

Deployment uses Docker Swarm rolling updates to reduce application interruption.

## 7. Rollback Procedure

If a release causes an application problem:

1. Identify the previously working release version.
2. Confirm that its Docker image exists in Amazon ECR.
3. Run the Jenkins pipeline with `ACTION=rollback`.
4. Specify the previous release using the `VERSION` parameter.
5. Redeploy the existing image to Docker Swarm.
6. Verify that the application is running the selected version.

Rollback must use the existing ECR image. It must not rebuild the application or create a new release image.

## 8. Release History

The initial release plan includes:

| Release | Description |
|---|---|
| v1.0.0 | Initial HW3 release |
| v1.1.0 | First feature update |
| v1.2.0 | Second feature update |

At least three GitHub releases must be created and verified.

A rollback demonstration must also be completed using an earlier published version.

## 9. Verification

For each release, verify:

- The GitHub tag exists.
- Jenkins successfully executes the release pipeline.
- The corresponding Docker image exists in Amazon ECR.
- Docker Swarm successfully deploys the selected image.
- The application is accessible and running the expected version.

For rollback, verify that the earlier version is running without rebuilding or pushing a new image.

## 10. Release Management Principles

- Use semantic versioning consistently.
- Never overwrite an existing release image tag.
- Keep HW2 and HW3 deployment resources separate.
- Use Jenkins credentials rather than storing passwords or AWS keys in source code.
- Verify every deployment and rollback.
- Maintain release documentation and evidence.