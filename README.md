# IQA Office Management System

A web-based office management application built with PHP, MariaDB, and Docker.

## Features

- Dashboard for navigating the application
- View office departments
- View employees and their associated departments
- Store application data in a MariaDB database
- Run the web application and database using Docker Compose

## Technology Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP with PDO
- **Database:** MariaDB 11.4
- **Web Server:** Apache
- **Containerization:** Docker and Docker Compose
- **Version Control:** Git

## Project Structure

```text
act_hw2/
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
├── .gitignore
├── Dockerfile
├── docker-compose.yml
└── README.md
```

## Prerequisites

- Docker Desktop
- Git

## Run the Application

From the project directory, start the containers:

```bash
docker compose up -d --build
```

Open the application in your browser:

- Dashboard: http://localhost:9000/
- Employees: http://localhost:9000/employees.php
- Departments: http://localhost:9000/departments.php

## Database

The database is named `iqa_hw2_db`.

The initialization script `database/init.sql` creates the `departments` and `employees` tables and inserts sample department records.

The PHP application connects to MariaDB through PDO using the Docker Compose database service.

## Useful Docker Commands

Check running containers:

```bash
docker compose ps
```

View service logs:

```bash
docker compose logs
```

Stop the services:

```bash
docker compose down
```

The database uses a persistent Docker volume, so stopping the services does not normally delete stored database records.

## Current Scope

The application currently supports viewing employee and department records. Creating, editing, and deleting records are not yet implemented.

## Author

VIBOL

## Year

2026

## Git Workflow

This project uses Git and GitHub for version control.

- `main` is the main project branch.
- Feature branches are used to organize changes separately from `main`.
- Pull requests are used to review changes before merging them into `main`.
- Jenkins is used to build the Docker image, push it to Amazon ECR, and deploy the application to Docker Swarm.

### Feature Branch Example

```bash
git switch -c feature/github-pr-workflow
git push -u origin feature/github-pr-workflow
```

After pushing a feature branch, open a pull request on GitHub to propose the changes for merging into `main`.

Webhook test: verifying automatic Jenkins builds after GitHub pushes.

Webhook verification follow-up: October 29, 2026.

Webhook test after Jenkins branch configuration update.
