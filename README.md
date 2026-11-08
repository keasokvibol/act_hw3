# IQA Office Management System — HW3

**Advanced Cloud Computing Technology**
**Homework 3: Release Management — GitHub Tags & Jenkins Build/Push/Deploy/Rollback**

---

## 1. Project Overview

The IQA Office Management System is a web-based application developed as part of Homework 3 for the Advanced Cloud Computing Technology course.

This project demonstrates the implementation of a release management and deployment workflow using GitHub, Jenkins, Docker, Amazon Elastic Container Registry (Amazon ECR), and Docker Swarm.

The main objectives are to:

- Manage application releases using Git tags and Semantic Versioning.
- Build Docker images corresponding to specific release versions.
- Store versioned Docker images in Amazon ECR.
- Automate build, push, and deployment activities through Jenkins.
- Deploy the application to a Docker Swarm cluster.
- Perform rollback by redeploying a previously published Docker image without rebuilding it.

## 2. Application Features

The application provides the following features:

### 2.1 Dashboard
- Display total employee statistics.
- Display total department statistics.
- Display employee distribution by department (introduced in v1.2.0).
- Display the current application release version.

### 2.2 Employee Management
- Display employee information.
- Present employee records through the employee management page.

### 2.3 Department Management
- Display department information.
- Present department records through the department management page.

### 2.4 Release Versioning
- Display the application version dynamically using the `APP_VERSION` environment variable.
- Support multiple application releases.
- Demonstrate rollback to a previous release.

## 3. Technology Stack

| Category | Technology |
|---|---|
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP 8.5 |
| Web Server | Apache |
| Database | MariaDB 11.4 |
| Database Access | PHP PDO |
| Containerization | Docker |
| Local Development | Docker Compose |
| Container Orchestration | Docker Swarm |
| Source Code Management | Git and GitHub |
| CI/CD | Jenkins |
| Container Registry | Amazon ECR |
| Cloud Infrastructure | Amazon EC2 |
| Cloud Provider | Amazon Web Services (AWS) |

## 4. System Architecture

The application uses a three-service architecture:

1. **Web Application:** Apache and PHP serve the application.
2. **Database:** MariaDB stores application data.
3. **Deployment and Orchestration:** Docker Swarm manages the application services in the production environment.

### 4.1 CI/CD and Release Management Architecture

The release management workflow is:

```text
Developer
    |
    v
GitHub Repository
    |
    | Git Tag (v1.0.0, v1.1.0, v1.2.0)
    v
Jenkins Pipeline
    |
    v
Build Docker Image
    |
    v
Push Versioned Image
to Amazon ECR
    |
    v
Deploy to Docker Swarm
    |
    v
Live Web Application
```

### 4.2 Rollback Architecture

The rollback process redeploys an existing Docker image from Amazon ECR.

```text
Operator
    |
    v
Jenkins Pipeline
    |
    | ACTION=rollback
    | VERSION=v1.1.0
    v
Verify Existing ECR Image
    |
    v
Docker Swarm Deployment
    |
    v
Application Restored
to v1.1.0
```

The rollback process does not rebuild or push a new Docker image.

## 5. Project Structure

The main project structure is:

```text
act_hw3/
│
├── database/
│   └── init.sql
│
├── docs/
│   ├── report/
│   │   └── HW3_Final_Submission_Report.docx
│   │
│   └── evidences/
│       ├── EVIDENCE_INDEX.md
│       ├── 01-release-v1.0.0/
│       ├── 02-release-v1.1.0/
│       ├── 03-release-v1.2.0/
│       ├── 04-amazon-ecr/
│       ├── 05-rollback-to-v1.1.0/
│       └── 06-jenkins-pipeline/
│
├── includes/
│   └── db.php
│
├── public/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   ├── departments.php
│   ├── employees.php
│   └── index.php
│
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

**Note:** `Jenkinsfile` and the original Docker configuration files are retained separately from the HW3-specific files to avoid interfering with the earlier homework project configuration.

The `.env` file contains local configuration and must not be committed to GitHub.

## 6. Application Deployment

### 6.1 Local Deployment Using Docker Compose

The HW3-specific Compose configuration is:

```text
docker-compose-hw3.yml
```

To start the local application:

```bash
docker compose -f docker-compose-hw3.yml up --build -d
```

To check the running containers:

```bash
docker compose -f docker-compose-hw3.yml ps
```

To view application logs:

```bash
docker compose -f docker-compose-hw3.yml logs
```

To stop the application:

```bash
docker compose -f docker-compose-hw3.yml down
```

The local application is accessible at:

**http://localhost:9003**

### 6.2 Production Deployment Using Docker Swarm

The HW3 production deployment configuration is:

```text
docker-stack-hw3.yml
```

The production environment uses:

- Amazon ECR for Docker image storage.
- Docker Swarm for container orchestration.
- Docker Swarm secrets for database credentials.
- Persistent storage for database data.
- Rolling updates for application deployment.

The production application is accessible at:

**http://18.215.157.112:9003**

The public IP address may change if the EC2 infrastructure is replaced or its public IP is reassigned.

## 7. Release Management

### 7.1 Semantic Versioning

This project follows Semantic Versioning:

```text
vMAJOR.MINOR.PATCH
```

| Component | Meaning |
|---|---|
| MAJOR | Incompatible or major changes |
| MINOR | New functionality |
| PATCH | Backward-compatible bug fixes |

### 7.2 Implemented Releases

Three releases were created and published:

| Release | Description |
|---|---|
| v1.0.0 | Initial application release |
| v1.1.0 | Dashboard improvements and application enhancements |
| v1.2.0 | Added employee distribution by department |

### 7.3 Git Release Commands

The following commands demonstrate the release tagging process:

```bash
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

For subsequent releases:

```bash
git tag -a v1.1.0 -m "Release v1.1.0"
git push origin v1.1.0
```

```bash
git tag -a v1.2.0 -m "Release v1.2.0"
git push origin v1.2.0
```

To view available tags:

```bash
git tag
```

To verify remote tags:

```bash
git ls-remote --tags origin
```

### 7.4 Release Workflow

The release workflow consists of:

1. Develop and test application changes.
2. Commit changes to GitHub.
3. Create an annotated Git tag.
4. Push the tag to GitHub.
5. Allow Jenkins to discover the release tag.
6. Build the Docker image using the corresponding release version.
7. Push the image to Amazon ECR.
8. Deploy the versioned image to Docker Swarm.
9. Verify the deployed application.

Each release uses its corresponding Git tag as the Docker image tag.

## 8. Jenkins Pipeline

The HW3 release management pipeline is defined in:

```text
Jenkinsfile.hw3
```

The Jenkins pipeline supports four actions:

| Parameter | Purpose |
|---|---|
| `build` | Build the Docker image |
| `push` | Push the image to Amazon ECR |
| `deploy` | Deploy an existing version to Docker Swarm |
| `rollback` | Restore a previous version using an existing ECR image |

The pipeline also uses a `VERSION` parameter to identify the release version.

### 8.1 Jenkins Pipeline Stages

The pipeline includes the following major activities:

1. Validate parameters and release version.
2. Check out the specified Git release tag when building a release.
3. Build the Docker image.
4. Push the image to Amazon ECR.
5. Verify the requested image exists in ECR.
6. Deploy the image to Docker Swarm.
7. Verify the deployment status.

For rollback, the build and push stages are skipped. The pipeline verifies and redeploys the existing image.

### 8.2 Amazon ECR Configuration

The Amazon ECR repository used for this project is:

```text
act_hw3_iqa_system
```

Repository URI:

```text
148676267942.dkr.ecr.us-east-1.amazonaws.com/act_hw3_iqa_system
```

AWS Region:

```text
us-east-1
```

The repository uses image tag immutability to prevent existing image tags from being overwritten.

The versioned images include:

```text
act_hw3_iqa_system:v1.0.0
act_hw3_iqa_system:v1.1.0
act_hw3_iqa_system:v1.2.0
```

Each release is retained as a separate versioned Docker image.

## 9. Rollback Procedure

### 9.1 Rollback Configuration

The Jenkins pipeline supports manual rollback using two parameters:

```text
ACTION=rollback
VERSION=v1.1.0
```

The `VERSION` parameter identifies the previously released version to restore.

### 9.2 Rollback Process

The rollback procedure is:

1. Open the HW3 Jenkins release management job.
2. Select **Build with Parameters**.
3. Set `ACTION` to `rollback`.
4. Set `VERSION` to the required previous version.
5. Start the Jenkins pipeline.
6. Verify that the requested image exists in Amazon ECR.
7. Redeploy the existing image to Docker Swarm.
8. Verify the running service and application version.

### 9.3 Actual Rollback Verification

A real rollback exercise was successfully completed.

| Item | Result |
|---|---|
| Previous running version | v1.2.0 |
| Rollback target | v1.1.0 |
| Jenkins action | rollback |
| Existing ECR image | Verified |
| Docker image rebuild | Skipped |
| ECR image push | Skipped |
| Docker Swarm deployment | Successful |
| Final application version | v1.1.0 |

The application was successfully restored from v1.2.0 to v1.1.0.

The rollback reused the existing image:

```text
act_hw3_iqa_system:v1.1.0
```

No new Docker image was built or pushed during the rollback.

The Docker Swarm service returned to a running state after the deployment.

## 10. Verification and Evidence

The following activities were completed and verified:

- Three annotated GitHub release tags were created and published.
- Jenkins discovered all three release tags.
- Docker images for v1.0.0, v1.1.0, and v1.2.0 were built and pushed to Amazon ECR.
- Jenkins successfully deployed the releases to Docker Swarm.
- The application was accessed through its public IP address.
- Dashboard statistics and employee distribution were tested.
- A real rollback from v1.2.0 to v1.1.0 was successfully executed.
- The rollback reused the existing ECR image without rebuilding or pushing a new image.

### 10.1 Evidence Repository

Screenshots are organized in:

```text
docs/evidences/
```

| Evidence folder | Description |
|---|---|
| [01-release-v1.0.0](docs/evidences/01-release-v1.0.0/) | Application screenshots for v1.0.0 |
| [02-release-v1.1.0](docs/evidences/02-release-v1.1.0/) | Application screenshots for v1.1.0 |
| [03-release-v1.2.0](docs/evidences/03-release-v1.2.0/) | Dashboard and employee distribution screenshots |
| [04-amazon-ecr](docs/evidences/04-amazon-ecr/) | Amazon ECR repository configuration |
| [05-rollback-to-v1.1.0](docs/evidences/05-rollback-to-v1.1.0/) | Application screenshots after rollback |
| [06-jenkins-pipeline](docs/evidences/06-jenkins-pipeline/) | Jenkins pipeline stage view |

See the [Evidence Index](docs/evidences/EVIDENCE_INDEX.md) for additional details.

Supporting Jenkins console logs, GitHub tag screenshots, and the ECR image list can also be added to the corresponding evidence folders.

## 11. Security and Configuration Notes

- Database credentials are managed through environment variables and Docker Swarm secrets.
- Sensitive configuration files must not be committed to GitHub.
- The `.env` file is excluded from version control.
- AWS credentials and access keys must be protected.
- Jenkins credentials are managed through Jenkins credentials configuration.
- Amazon ECR stores the versioned Docker images.
- The production database uses persistent Docker storage.

## 12. Author

**VIBOL**

Advanced Cloud Computing Technology
Homework 3 — Release Management

## 13. Repository

GitHub Repository:

[https://github.com/keasokvibol/act_hw3](https://github.com/keasokvibol/act_hw3)

Jenkins:

[http://54.226.226.120:9090](http://54.226.226.120:9090)

Production Application:

[http://18.215.157.112:9003](http://18.215.157.112:9003)

---

**End of README**
