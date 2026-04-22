# cron_scheduler
# Cron Scheduler — Service Health API

A fintech-inspired service health monitoring API built with Laravel. 
It exposes liveness and readiness probes alongside a cron job monitor 
that tracks the status of critical scheduled financial operations.

---

## Running the App Locally

1. Clone the repository
   git clone https://github.com/winnie20-sam/cron_scheduler.git
   cd cron_scheduler

2. Copy the environment file
   cp .env.example .env

3. Copy the environment file and fill in your own values
   cp .env.example .env

4. Start the containers
   docker compose up -d --build

5. Run migrations and seed the database
   docker compose exec app php artisan migrate --seed

6. Visit the app at http://localhost:8080

---

## API Endpoints

| Method | Endpoint            | Description                        |
|--------|---------------------|------------------------------------|
| GET    | /api/health         | Liveness probe — is the app alive? |
| GET    | /api/health/ready   | Readiness probe — are all dependencies healthy? |
| GET    | /api/jobs           | Lists all cron jobs and their status |
| POST   | /api/jobs/{id}/run  | Manually trigger a failed job |

---

## CI/CD Pipeline Stages

Stage 1 — Lint and Test
Runs on every Pull Request. Spins up a MySQL container, installs Composer
dependencies, runs migrations, and executes php artisan test. A PR cannot
be merged if this stage fails.

Stage 2 — Build Docker Image
Runs on every push to main. Builds the Docker image using the Dockerfile
and tags it with the short commit SHA so every build is uniquely
identifiable.

Stage 3 — Push to Docker Hub
Runs immediately after the build stage. Logs into Docker Hub using stored
secrets and pushes both the SHA-tagged image and the latest tag.

Stage 4 — Manual Approval Gate
The pipeline pauses here and sends a notification to the required reviewer.
No deployment can happen until a human clicks Approve in the GitHub
Actions UI under the production environment.

Stage 5 — Deploy to Production
Runs only after approval is granted. SSHs into the production server,
pulls the new image from Docker Hub, stops the old container, and starts
the new one with environment variables injected at runtime.

---

## Architecture

Internet
    |
Load Balancer (public subnet)
    |
ECS Fargate — Laravel App (private subnet)
    |
RDS MySQL 8.0 (private subnet)
    |
AWS Secrets Manager (APP_KEY, DB_PASSWORD)

All secrets are read from AWS Secrets Manager at runtime.
The database is never exposed to the internet.
Only the load balancer sits in the public subnet.

---

## Assumptions

- The production server has Docker installed and is accessible via SSH.
- A Docker Hub account exists and credentials are stored as GitHub secrets.
- The .env.example file is committed with placeholder values only, never real credentials.
- AWS was chosen as the cloud provider since the app uses MySQL which maps directly to RDS.
- Terraform is written for review purposes and is not applied against a live account.

---

## One Thing I Would Improve With More Time
I would add an automated rollback mechanism to the deploy stage. 
Currently if a deployment fails for any reason the app could be 
left in a broken state until someone manually intervenes. I would implement an automatic rollback that detects any failure 
after deployment and immediately restarts the last known working 
image tag

The rollback would also trigger an automated alert to the 
team via Slack or email so the failure does not go unnoticed 
while the app continues running on the previous stable version.

