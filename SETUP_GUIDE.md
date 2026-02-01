# Setup Guide - Veterinary Farm Management System

## Prerequisites

Before you begin, ensure you have the following installed:

- **Node.js**: v18.x or higher ([Download](https://nodejs.org/))
- **PostgreSQL**: v14.x or higher ([Download](https://www.postgresql.org/download/))
- **npm**: v9.x or higher (comes with Node.js)
- **Git**: For version control

## Installation Steps

### 1. Database Setup

#### Create PostgreSQL Database

```bash
# Open PostgreSQL command line or pgAdmin
psql -U postgres

# Create database
CREATE DATABASE veterinary_db;

# Create user (optional)
CREATE USER vet_admin WITH ENCRYPTED PASSWORD 'your_password';
GRANT ALL PRIVILEGES ON DATABASE veterinary_db TO vet_admin;

# Exit psql
\q
```

### 2. Backend Setup

```bash
# Navigate to server directory
cd server

# Install dependencies
npm install

# Copy environment file
cp .env.example .env
```

#### Configure Environment Variables

Edit `server/.env`:

```env
DATABASE_URL="postgresql://postgres:your_password@localhost:5432/veterinary_db"
JWT_SECRET="your-very-secret-jwt-key-change-this-in-production"
JWT_EXPIRES_IN="7d"
PORT=5000
NODE_ENV="development"
UPLOAD_DIR="./uploads"
```

**Important**: Change the `JWT_SECRET` to a secure random string in production!

#### Initialize Database

```bash
# Generate Prisma client
npx prisma generate

# Run migrations
npx prisma migrate dev --name init

# Seed database with sample data
npm run prisma:seed
```

### 3. Frontend Setup

```bash
# Navigate to client directory from root
cd ../client

# Install dependencies
npm install

# Copy environment file
cp .env.example .env
```

#### Configure Environment Variables

Edit `client/.env`:

```env
VITE_API_URL=http://localhost:5000/api
```

### 4. Running the Application

#### Option 1: Run Both (Recommended for Development)

From the root directory:

```bash
npm run dev
```

This will start both backend and frontend concurrently.

#### Option 2: Run Separately

**Terminal 1 - Backend:**
```bash
cd server
npm run dev
```

**Terminal 2 - Frontend:**
```bash
cd client
npm run dev
```

### 5. Access the Application

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:5000
- **API Health Check**: http://localhost:5000/health

### 6. Login Credentials

After seeding the database, use these credentials:

**Admin:**
- Email: `admin@vet.com`
- Password: `admin123`

**Veterinarian:**
- Email: `vet@vet.com`
- Password: `vet123`

**Staff:**
- Email: `staff@vet.com`
- Password: `staff123`

## Troubleshooting

### Database Connection Issues

1. Ensure PostgreSQL is running:
   ```bash
   # Windows
   pg_ctl status

   # Linux/Mac
   sudo service postgresql status
   ```

2. Verify database credentials in `.env`

3. Test connection:
   ```bash
   psql -U postgres -d veterinary_db
   ```

### Port Already in Use

If ports 5000 or 5173 are already in use:

1. Change backend port in `server/.env`:
   ```env
   PORT=5001
   ```

2. Update frontend API URL in `client/.env`:
   ```env
   VITE_API_URL=http://localhost:5001/api
   ```

3. Change frontend port in `client/vite.config.ts`:
   ```typescript
   server: {
     port: 5174,
   }
   ```

### Module Not Found Errors

```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

### Prisma Client Issues

```bash
# Regenerate Prisma client
cd server
npx prisma generate
```

### Build Errors

```bash
# Clear build cache
rm -rf dist build .vite

# Rebuild
npm run build
```

## Development Tools

### Database Management

**View Database:**
```bash
cd server
npx prisma studio
```

This opens Prisma Studio at http://localhost:5555 for viewing and editing database records.

### API Testing

Use tools like:
- **Postman**: Import API endpoints
- **cURL**: Command-line testing
- **Thunder Client**: VS Code extension

### Code Quality

```bash
# Lint code
npm run lint

# Format code (if configured)
npm run format
```

## Next Steps

1. **Customize**: Modify the application to match your specific needs
2. **Add Features**: Implement additional modules from the PRD
3. **Security**: Change default passwords and JWT secrets
4. **Testing**: Add unit and integration tests
5. **Deploy**: Follow the deployment guide for production setup

## Support

For issues or questions:
1. Check the [README.md](README.md)
2. Review [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
3. Open an issue on GitHub
4. Contact the development team

## Important Security Notes

⚠️ **Before deploying to production:**

1. Change all default passwords
2. Use strong JWT secret
3. Enable HTTPS
4. Configure CORS properly
5. Set up rate limiting
6. Enable database backups
7. Use environment-specific configurations
8. Never commit `.env` files to version control
