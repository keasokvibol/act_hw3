# IQA Office Management System — HW3

**Advanced Cloud Computing Technology — Homework 3**
**Release Management: GitHub Tags & Jenkins Build/Push/Deploy/Rollback**

## 1. Project Overview

The IQA Office Management System is a web-based application developed using PHP, MariaDB, HTML, CSS, and JavaScript.

This project demonstrates automated release management and deployment using GitHub, Jenkins, Amazon Elastic Container Registry (ECR), and Docker Swarm.

The main objective is to implement version-controlled releases, automated Docker image management, deployment, and rollback to a previous application version.

## 2. Application Features

- Dashboard displaying total employees and departments.
- Employee management: add, view, edit, and delete employee records.
- Department management: add, view, edit, and delete department records.
- Employee and department relationships.
- Employee distribution by department (introduced in v1.2.0).
- Dynamic application version display.
- Persistent MariaDB database storage.

## 3. Technology Stack

| Component | Technology |
|---|---|
| Frontend | HTML5, CSS3, JavaScript |
| Backend | PHP 8.5 with PDO |
| Database | MariaDB 11.4 |
| Web Server | Apache |
| Containerization | Docker |
| Local orchestration | Docker Compose |
| Production orchestration | Docker Swarm |
| Version control | Git and GitHub |
| CI/CD | Jenkins |
| Container registry | Amazon ECR |
| Cloud platform | Amazon Web Services (AWS) |

## 4. System Architecture

The application uses the following release and deployment workflow:

```text
Developer
    |
    v
GitHub Repository
    |
    | Git Tag (v1.0.0, v1.1.0, v1.2.0)
    v
Jenkins Multibranch Pipeline
    |
    v
Build Docker Image
    |
    v
Amazon ECR
    |
    v
Docker Swarm Manager
    |
    v
IQA Office Management System
    |
    v
Public Application (Port 9003)
```

### Rollback Architecture

```text
Jenkins Build with Parameters
    |
    | ACTION=rollback
    | VERSION=v1.1.0
    v
Verify Existing Image in Amazon ECR
    |
    v
Skip Docker Build
    |
    v
Skip Image Push
    |
    v
Redeploy Existing Image
    |
    v
Docker Swarm Rolling Update
    |
    v
Previous Application Version Restored
```

## 5. Project Structure

```text
act_hw3/
├── database/
│   └── init.sql
├── includes/
│   └── db.php
├── public/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── app.js
│   ├── departments.php
│   ├── employees.php
│   └── index.php
├── .env
├── .gitignore
├── Dockerfile
├── docker-compose.yml
├── docker-compose-hw3.yml
├── docker-stack.yml
├── docker-stack-hw3.yml
├── Jenkinsfile
├── Jenkinsfile.hw3
├── README.md
└── RELEASING.md
```

**Note:** `Jenkinsfile` and `docker-compose.yml` are retained separately from the HW2 configuration. HW3 uses `Jenkinsfile.hw3`, `docker-compose-hw3.yml`, and `docker-stack-hw3.yml`.

## 6. Application Deployment

### 6.1 Local Deployment Using Docker Compose

Prerequisites:

- Docker Desktop
- Git
- VS Code (recommended)

From the project directory, configure the required environment variables in `.env` without committing credentials to GitHub.

Start the local HW3 application:

```bash
docker compose -f docker-compose-hw3.yml up --build -d
```

Check running containers:

```bash
docker compose -f docker-compose-hw3.yml ps
```

Access the application:

- Dashboard: http://localhost:9003/
- Employees: http://localhost:9003/employees.php
- Departments: http://localhost:9003/departments.php

Stop local services:

```bash
docker compose -f docker-compose-hw3.yml down
```

### 6.2 Production Deployment

The production application is deployed using Docker Swarm.

| Configuration | Value |
|---|---|
| Stack name | `iqa-hw3` |
| Web service | `iqa-hw3_web` |
| Database service | `iqa-hw3_db` |
| Application port | `9003` |
| Database | `iqa_hw3_db` |
| Database image | `mariadb:11.4` |

Public application URL:

**http://18.215.157.112:9003**

## 7. Release Management

This project follows Semantic Versioning:

```text
vMAJOR.MINOR.PATCH
```

Three releases were implemented and deployed.

| Version | Release description | Main improvement |
|---|---|---|
| v1.0.0 | Initial application release | IQA Office Management System |
| v1.1.0 | Dashboard enhancement | Live dashboard statistics and dynamic version display |
| v1.2.0 | Dashboard enhancement | Employee distribution by department |

### 7.1 Git Release Commands

Create an annotated release tag:

```bash
git tag -a v1.0.0 -m "Release v1.0.0"
```

Push a release tag to GitHub:

```bash
git push origin v1.0.0
```

List available tags:

```bash
git tag
```

View release information:

```bash
git show --no-patch --decorate v1.1.0
```

### 7.2 Release Workflow

1. Develop and test application changes.
2. Commit the changes to the main branch.
3. Push the latest code to GitHub.
4. Create an annotated Git release tag.
5. Push the tag to GitHub.
6. Jenkins discovers the release tag.
7. Jenkins builds the Docker image using the release tag.
8. Jenkins pushes the image to Amazon ECR.
9. Jenkins deploys the corresponding image to Docker Swarm.
10. Verify the deployed application version.

## 8. Jenkins Pipeline

The HW3 release pipeline is maintained in:

```text
Jenkinsfile.hw3
```

Jenkins job configuration:

- Multibranch Pipeline: `ACT-HW3-RELEASE-TAGS`
- Parameterized Pipeline: `ACT-HW3-RELEASE-MANAGEMENT`

The release pipeline supports four actions:

| ACTION | Description |
|---|---|
| `build` | Build the Docker image |
| `push` | Push the image to Amazon ECR |
| `deploy` | Deploy an existing image to Docker Swarm |
| `rollback` | Restore a previously published image |

Pipeline parameters:

- `ACTION`: Selected release management operation.
- `VERSION`: Semantic version of the target release.

### 8.1 Amazon ECR Configuration

ECR repository:

```text
act_hw3_iqa_system
```

Repository URI:

```text
148676267942.dkr.ecr.us-east-1.amazonaws.com/act_hw3_iqa_system
```

Published image versions:

```text
act_hw3_iqa_system:v1.0.0
act_hw3_iqa_system:v1.1.0
act_hw3_iqa_system:v1.2.0
```

Each release is stored using its own image tag. Existing release tags are not overwritten.

## 9. Rollback Procedure

Rollback allows the application to return to a previously released version without rebuilding or pushing a new Docker image.

The rollback exercise was successfully performed using Jenkins.

### 9.1 Rollback Configuration

Open the Jenkins job:

```text
ACT-HW3-RELEASE-MANAGEMENT
```

Select **Build with Parameters** and specify:

```text
ACTION  = rollback
VERSION = v1.1.0
```

Execute the pipeline.

### 9.2 Rollback Process

1. Jenkins validates the rollback parameters.
2. Jenkins verifies that the requested image exists in Amazon ECR.
3. Jenkins skips the Docker build stage.
4. Jenkins skips the ECR push stage.
5. Jenkins redeploys the existing image to Docker Swarm.
6. Docker Swarm applies the configured rolling update.
7. The application returns to the selected previous version.

### 9.3 Actual Rollback Verification

The project successfully performed a rollback from `v1.2.0` to `v1.1.0`.

Jenkins Console Output confirmed:

```text
HW3 rollback
Version: v1.1.0
Action: rollback

Existing ECR image verified.

Build Docker Image: SKIPPED
Push Image to Amazon ECR: SKIPPED

Deploying HW3 stack: iqa-hw3

HW3 PIPELINE SUCCESSFUL
Release: v1.1.0
Action: rollback

Finished: SUCCESS
```

The deployed web service was verified using the existing ECR image:

```text
act_hw3_iqa_system:v1.1.0
```

No new Docker image was built or pushed during the rollback.

## 10. Verification and Evidence

The following activities were completed and verified:

- Three annotated GitHub release tags were created and published.
- Jenkins successfully discovered all three release tags.
- Docker images for v1.0.0, v1.1.0, and v1.2.0 were built and pushed to Amazon ECR.
- Jenkins successfully deployed each release to Docker Swarm.
- The application was accessed through its public IP address.
- Dashboard statistics and employee distribution were tested.
- A real rollback from v1.2.0 to v1.1.0 was successfully executed.
- The rollback reused the existing ECR image without rebuilding or pushing.

Screenshots and Jenkins Console Output are retained as evidence for the homework submission.

## 11. Security and Configuration Notes

- Database credentials are managed through environment variables and Docker Swarm secrets.
- Sensitive configuration files and credentials must not be committed to GitHub.
- Amazon ECR is used to store versioned Docker images.
- Jenkins uses AWS permissions to access Amazon ECR.
- The production database uses persistent Docker storage.

## 12. Author

**VIBOL**

Advanced Cloud Computing Technology
Homework 3 — Release Management
2026

## 13. Repository

GitHub:

https://github.com/keasokvibol/act_hw3

---

*End of README*
