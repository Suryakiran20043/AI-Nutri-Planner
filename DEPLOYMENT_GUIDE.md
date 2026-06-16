# DEPLOYMENT GUIDE (AWS + Docker)

## 1. Prerequisites
- AWS Account with IAM permissions for EKS, ECR, RDS, S3.
- Docker & Docker Compose installed locally.
- AWS CLI configured locally.

## 2. Docker Configuration
### `docker-compose.yml`
```yaml
version: '3.8'
services:
  php-backend:
    build: ./backend
    ports:
      - "8000:8000"
    environment:
      - DB_HOST=db
      - DB_NAME=nutriplan
      - AI_SERVICE_URL=http://ai-service:8001
    depends_on:
      - db
      - ai-service

  ai-service:
    build: ./python_ai_service
    ports:
      - "8001:8001"
    deploy:
      resources:
        reservations:
          devices:
            - driver: nvidia
              count: 1
              capabilities: [gpu]

  frontend:
    build: ./frontend
    ports:
      - "80:80"

  db:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=secret
      - MYSQL_DATABASE=nutriplan
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
```

## 3. AWS Deployment (Production)

### AWS RDS (MySQL)
1. Provision an RDS MySQL 8.0 instance (Multi-AZ for high availability).
2. Configure Security Groups to only allow incoming traffic from the EKS cluster nodes.

### AWS ECR (Elastic Container Registry)
1. Create three repositories: `nutriplan-frontend`, `nutriplan-backend`, `nutriplan-ai`.
2. Build and push your Docker images to ECR:
   ```bash
   aws ecr get-login-password --region us-east-1 | docker login --username AWS --password-stdin <aws_account_id>.dkr.ecr.us-east-1.amazonaws.com
   docker build -t nutriplan-frontend ./frontend
   docker tag nutriplan-frontend:latest <aws_account_id>.dkr.ecr.us-east-1.amazonaws.com/nutriplan-frontend:latest
   docker push <aws_account_id>.dkr.ecr.us-east-1.amazonaws.com/nutriplan-frontend:latest
   ```

### AWS EKS (Elastic Kubernetes Service)
1. Create an EKS cluster using `eksctl` or Terraform.
2. Ensure you have a Node Group with GPU instances (e.g., `g4dn.xlarge`) for the `ai-service` deployment to accelerate OCR and LLM tasks.
3. Deploy the manifests (`deployment.yaml`, `service.yaml`, `ingress.yaml`).

### AWS S3 (Storage)
1. Create a private S3 bucket: `nutriplan-medical-reports`.
2. Update the Laravel backend to use the S3 disk driver instead of the local filesystem for uploading and storing user medical reports securely.

## 4. CI/CD Pipeline (GitHub Actions)
Configure `.github/workflows/deploy.yml` to automatically build Docker images, run PHPUnit and PyTest, and update the EKS cluster upon merging into the `main` branch.
