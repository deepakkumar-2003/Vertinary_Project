# Deployment Guide

This guide covers deploying the Veterinary Farm Management System to production.

## Pre-Deployment Checklist

- [ ] All tests passing
- [ ] Environment variables configured
- [ ] Database backup strategy in place
- [ ] SSL/TLS certificates obtained
- [ ] Domain name configured
- [ ] Security audit completed
- [ ] Performance optimization done
- [ ] Monitoring tools set up

---

## Environment Configuration

### Production Environment Variables

**Backend (.env):**
```env
NODE_ENV=production
PORT=5000
DATABASE_URL=postgresql://user:password@production-db-host:5432/veterinary_db
JWT_SECRET=<strong-random-secret-minimum-32-characters>
JWT_EXPIRES_IN=7d
UPLOAD_DIR=/var/uploads
```

**Frontend (.env.production):**
```env
VITE_API_URL=https://api.yourdomain.com/api
```

---

## Deployment Options

### Option 1: Traditional Server (VPS/Dedicated)

#### Requirements
- Ubuntu 20.04+ or similar
- Node.js 18+
- PostgreSQL 14+
- Nginx
- PM2 (Process Manager)

#### Setup Steps

**1. Server Setup:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install PostgreSQL
sudo apt install postgresql postgresql-contrib -y

# Install Nginx
sudo apt install nginx -y

# Install PM2
sudo npm install -g pm2
```

**2. Database Setup:**
```bash
# Switch to postgres user
sudo -u postgres psql

# Create database and user
CREATE DATABASE veterinary_db;
CREATE USER vet_admin WITH ENCRYPTED PASSWORD 'strong_password';
GRANT ALL PRIVILEGES ON DATABASE veterinary_db TO vet_admin;
\q
```

**3. Deploy Backend:**
```bash
# Clone repository
cd /var/www
git clone <repository-url> veterinary-app
cd veterinary-app

# Install dependencies
cd server
npm install --production

# Setup environment
cp .env.example .env
nano .env  # Edit with production values

# Run migrations
npx prisma migrate deploy
npx prisma generate

# Build
npm run build

# Start with PM2
pm2 start dist/index.js --name veterinary-api
pm2 save
pm2 startup
```

**4. Deploy Frontend:**
```bash
cd ../client
npm install
npm run build

# Copy build to nginx directory
sudo cp -r dist /var/www/veterinary-frontend
```

**5. Configure Nginx:**

Create `/etc/nginx/sites-available/veterinary`:

```nginx
# Backend API
server {
    listen 80;
    server_name api.yourdomain.com;

    location / {
        proxy_pass http://localhost:5000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

# Frontend
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;

    root /var/www/veterinary-frontend;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable site and restart nginx:
```bash
sudo ln -s /etc/nginx/sites-available/veterinary /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

**6. SSL Setup (Let's Encrypt):**
```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com -d api.yourdomain.com
```

---

### Option 2: Docker Deployment

**1. Create Dockerfile for Backend:**

`server/Dockerfile`:
```dockerfile
FROM node:18-alpine

WORKDIR /app

COPY package*.json ./
RUN npm ci --production

COPY . .
RUN npx prisma generate
RUN npm run build

EXPOSE 5000

CMD ["npm", "start"]
```

**2. Create Dockerfile for Frontend:**

`client/Dockerfile`:
```dockerfile
FROM node:18-alpine as build

WORKDIR /app
COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

FROM nginx:alpine
COPY --from=build /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
```

**3. Create docker-compose.yml:**

```yaml
version: '3.8'

services:
  postgres:
    image: postgres:14-alpine
    environment:
      POSTGRES_DB: veterinary_db
      POSTGRES_USER: vet_admin
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - vet-network
    restart: unless-stopped

  backend:
    build: ./server
    environment:
      NODE_ENV: production
      DATABASE_URL: postgresql://vet_admin:${DB_PASSWORD}@postgres:5432/veterinary_db
      JWT_SECRET: ${JWT_SECRET}
      PORT: 5000
    depends_on:
      - postgres
    networks:
      - vet-network
    restart: unless-stopped
    command: sh -c "npx prisma migrate deploy && npm start"

  frontend:
    build: ./client
    ports:
      - "80:80"
      - "443:443"
    depends_on:
      - backend
    networks:
      - vet-network
    restart: unless-stopped

volumes:
  postgres_data:

networks:
  vet-network:
    driver: bridge
```

**4. Deploy:**
```bash
# Create .env file with secrets
echo "DB_PASSWORD=your_db_password" > .env
echo "JWT_SECRET=your_jwt_secret" >> .env

# Build and start
docker-compose up -d

# View logs
docker-compose logs -f
```

---

### Option 3: Cloud Platforms

#### Heroku

**Backend:**
```bash
# Install Heroku CLI
# Login
heroku login

# Create app
cd server
heroku create veterinary-api

# Add PostgreSQL
heroku addons:create heroku-postgresql:hobby-dev

# Set environment variables
heroku config:set JWT_SECRET=your-secret
heroku config:set NODE_ENV=production

# Deploy
git push heroku main

# Run migrations
heroku run npx prisma migrate deploy
```

**Frontend:**
```bash
cd client
# Deploy to Vercel, Netlify, or similar
# Update VITE_API_URL to Heroku backend URL
```

#### AWS (EC2 + RDS)

1. Create RDS PostgreSQL instance
2. Launch EC2 instance
3. Follow Traditional Server steps
4. Use RDS connection string in DATABASE_URL

#### DigitalOcean App Platform

1. Create new app
2. Connect GitHub repository
3. Configure build commands
4. Add PostgreSQL database
5. Set environment variables
6. Deploy

---

## Database Migration

### Zero-Downtime Migration

```bash
# 1. Backup current database
pg_dump -h localhost -U vet_admin veterinary_db > backup.sql

# 2. Test migration locally
DATABASE_URL="postgresql://..." npx prisma migrate deploy

# 3. Deploy to production
heroku run npx prisma migrate deploy
# OR
ssh production-server "cd /var/www/veterinary-app/server && npx prisma migrate deploy"
```

---

## Monitoring & Logging

### PM2 Monitoring
```bash
pm2 monit
pm2 logs veterinary-api
pm2 restart veterinary-api
```

### Log Management
```bash
# Install log rotation
sudo apt install logrotate

# Configure in /etc/logrotate.d/veterinary
/var/log/veterinary/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### Application Monitoring

Consider integrating:
- **Sentry** - Error tracking
- **DataDog** - Performance monitoring
- **LogRocket** - Session replay
- **Uptime Robot** - Uptime monitoring

---

## Backup Strategy

### Automated Database Backups

```bash
# Create backup script
cat > /usr/local/bin/backup-db.sh << 'EOF'
#!/bin/bash
BACKUP_DIR="/var/backups/veterinary"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR
pg_dump -h localhost -U vet_admin veterinary_db | gzip > $BACKUP_DIR/backup_$DATE.sql.gz
# Keep only last 30 days
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +30 -delete
EOF

chmod +x /usr/local/bin/backup-db.sh

# Schedule daily backups
crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-db.sh
```

---

## Security Hardening

### 1. Firewall Setup
```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 2. Fail2ban
```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 3. Security Headers (Nginx)

Add to nginx config:
```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
```

### 4. Regular Updates
```bash
# Automate security updates
sudo apt install unattended-upgrades -y
sudo dpkg-reconfigure --priority=low unattended-upgrades
```

---

## Performance Optimization

### Database Optimization
- Enable connection pooling
- Add database indexes
- Regular VACUUM and ANALYZE
- Monitor slow queries

### Backend Optimization
- Enable gzip compression
- Implement caching (Redis)
- Use CDN for static assets
- Optimize API queries

### Frontend Optimization
- Code splitting
- Lazy loading
- Image optimization
- PWA caching

---

## Rollback Procedure

```bash
# 1. Restore database backup
gunzip < backup_20240115.sql.gz | psql -h localhost -U vet_admin veterinary_db

# 2. Revert code deployment
git reset --hard <previous-commit>
pm2 restart veterinary-api

# 3. Clear caches if needed
redis-cli FLUSHALL
```

---

## Post-Deployment

1. **Test all endpoints**
2. **Verify database connections**
3. **Check monitoring dashboards**
4. **Test user authentication**
5. **Verify file uploads**
6. **Test critical workflows**
7. **Monitor error logs**
8. **Load testing**

---

## Maintenance

### Regular Tasks
- Weekly: Review logs
- Monthly: Security updates
- Quarterly: Performance audit
- Yearly: Disaster recovery drill

### Health Checks
```bash
# API health
curl https://api.yourdomain.com/health

# Database connection
psql -h localhost -U vet_admin -d veterinary_db -c "SELECT 1"

# Disk space
df -h

# Memory usage
free -m
```
