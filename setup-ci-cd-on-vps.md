# Setup GitHub CI/CD on VPS

This guide sets up automatic deployment from GitHub to your VPS for this project.

It uses:
- GitHub Actions for CI/CD
- SSH to connect to VPS
- Existing deploy script: scripts/deploy.sh
- Existing production compose file: docker-compose.prod.yml

## 1. VPS one-time setup

Run these commands on your VPS.

```bash
# 1) Create deploy user (if not already created)
sudo adduser deploy
sudo usermod -aG docker deploy

# 2) Create app directory
sudo mkdir -p /var/www/lotto
sudo chown -R deploy:deploy /var/www/lotto

# 3) Install git + docker + compose plugin
sudo apt update
sudo apt install -y git docker.io docker-compose-plugin
sudo systemctl enable docker
sudo systemctl start docker

# 4) Prepare SSH folder
sudo -u deploy mkdir -p /home/deploy/.ssh
sudo -u deploy chmod 700 /home/deploy/.ssh
```

## 2. Clone repository on VPS

Login as deploy and clone the project.

```bash
sudo -iu deploy
cd /var/www
git clone git@github.com:YOUR_ORG/YOUR_REPO.git lotto
cd /var/www/lotto
```

If your repo is private, make sure deploy user can read it (deploy key or SSH key with repo access).

## 3. Prepare production env file

```bash
cd /var/www/lotto
cp src/.env.example src/.env
```

Then edit src/.env with production values.

Important values:
- APP_ENV=production
- APP_DEBUG=false
- DB_HOST=db
- DB_DATABASE, DB_USERNAME, DB_PASSWORD

## 4. Configure host ports

Your current production mapping is:
- HTTP host port 80 -> container 80
- HTTPS host port 443 -> container 443

So app access is:
- http://YOUR_SERVER_IP:80

- https://YOUR_DOMAIN (when SSL is configured)

If UFW is enabled, allow ports:

```bash
sudo ufw allow 22
sudo ufw allow 81
sudo ufw allow 443
sudo ufw reload
```

## 5. Generate SSH key for GitHub Actions

On your local machine:

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/lotto_ci_ed25519
```

Copy public key to VPS deploy user:

```bash
ssh-copy-id -i ~/.ssh/lotto_ci_ed25519.pub deploy@YOUR_VPS_IP
```

Test login:

```bash
ssh -i ~/.ssh/lotto_ci_ed25519 deploy@YOUR_VPS_IP
```

## 6. Add GitHub repository secrets

In GitHub repo -> Settings -> Secrets and variables -> Actions, add:

- VPS_HOST: your server IP or domain
- VPS_PORT: 22
- VPS_USER: deploy
- VPS_APP_DIR: /var/www/lotto
- VPS_SSH_KEY: content of ~/.ssh/lotto_ci_ed25519

To copy private key content:

```bash
cat ~/.ssh/lotto_ci_ed25519
```

## 7. Create GitHub Actions workflow

Create file .github/workflows/deploy-vps.yml in your repo:

```yaml
name: Deploy to VPS

on:
  push:
    branches: ["Production"]
  workflow_dispatch:

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup SSH
        run: |
          mkdir -p ~/.ssh
          echo "${{ secrets.VPS_SSH_KEY }}" > ~/.ssh/id_ed25519
          chmod 600 ~/.ssh/id_ed25519
          ssh-keyscan -p "${{ secrets.VPS_PORT }}" "${{ secrets.VPS_HOST }}" >> ~/.ssh/known_hosts

      - name: Deploy on VPS
        run: |
          ssh -p "${{ secrets.VPS_PORT }}" "${{ secrets.VPS_USER }}@${{ secrets.VPS_HOST }}" \
            "cd ${{ secrets.VPS_APP_DIR }} && bash scripts/deploy.sh Production"
```

## 8. Ensure deploy script is executable

On VPS:

```bash
cd /var/www/lotto
chmod +x scripts/deploy.sh
```

## 9. First manual deployment test

Before CI/CD, run one manual deploy on VPS:

```bash
cd /var/www/lotto
bash scripts/deploy.sh Production
```

If this succeeds, GitHub Actions deployment should also succeed.

## 10. Verify deployment

After a push to Production:
- Open GitHub Actions and confirm job success
- Check containers on VPS:

```bash
docker compose -f /var/www/lotto/docker-compose.prod.yml ps
```

- Check logs if needed:

```bash
docker compose -f /var/www/lotto/docker-compose.prod.yml logs -f app
docker compose -f /var/www/lotto/docker-compose.prod.yml logs -f webserver
```

## Troubleshooting

1. Permission denied (publickey)
- Verify VPS_SSH_KEY secret is full private key
- Verify public key exists in /home/deploy/.ssh/authorized_keys
- Verify file permissions:

```bash
chmod 700 /home/deploy/.ssh
chmod 600 /home/deploy/.ssh/authorized_keys
```

2. docker: permission denied
- Ensure deploy user is in docker group:

```bash
sudo usermod -aG docker deploy
```

Then reconnect SSH session.

3. src/.env missing during deploy
- The deploy script will stop by design if src/.env is missing.
- Create src/.env first on VPS.

4. Port already in use
- Your project already uses host port 81 for HTTP in production compose.
- If conflict still exists, run:

```bash
sudo lsof -i :81
```

Then stop conflicting service or change host port mapping.
