# Docker Deployment Guide for Euroleague Stats

## Overview
This Laravel application uses:
- **Laravel 12** (Blade templates + some Inertia pages)
- **Vite** for asset bundling
- **Tailwind CSS 4**
- **Vue.js 3** (for Inertia pages if any)
- **SQLite** database

## Files for Deployment

### 1. Dockerfile
- Multi-stage build with PHP 8.2 + Apache
- Installs Node.js for Vite builds
- Builds CSS/JS assets during Docker build
- Sets up proper permissions
- Includes startup script for runtime configuration

### 2. render.yaml
- Blueprint configuration for Render.com
- Sets all required environment variables
- Configures SQLite database
- Sets up health checks

### 3. .dockerignore
- Excludes unnecessary files from Docker build
- Reduces image size

## Deployment to Render

### Method 1: Using Blueprint (render.yaml)

1. **Push your code to Git:**
   ```bash
   git add Dockerfile render.yaml .dockerignore
   git commit -m "Add Docker configuration"
   git push
   ```

2. **Create Blueprint on Render:**
   - Go to https://render.com
   - Click "New" → "Blueprint"
   - Connect your Git repository
   - Render will detect `render.yaml` and configure everything automatically
   - Click "Apply" to deploy

### Method 2: Manual Web Service

1. **Push your code to Git**

2. **Create Web Service:**
   - Go to https://render.com
   - Click "New" → "Web Service"
   - Connect your repository
   - Render will auto-detect the Dockerfile

3. **Configure Service:**
   - **Name:** euroleague-stats
   - **Region:** Frankfurt (or your preferred region)
   - **Instance Type:** Free (or paid)
   - **Docker Command:** Leave empty (uses CMD from Dockerfile)

4. **Add Environment Variables:**
   ```
   APP_NAME=EuroleagueStats
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:... (will be auto-generated)
   DB_CONNECTION=sqlite
   SESSION_DRIVER=database
   CACHE_STORE=database
   QUEUE_CONNECTION=database
   LOG_CHANNEL=stack
   LOG_LEVEL=error
   ```

5. **Deploy**

## Troubleshooting

### Issue: "Only HTML showing, no CSS/JS"

**Cause:** Vite assets not loading correctly

**Solutions:**

1. **Check build logs** - Ensure `npm run build` succeeded during Docker build
2. **Check manifest exists** - The startup script validates this
3. **Check Apache logs** on Render for 404 errors
4. **Verify APP_URL** is set correctly in Render dashboard

### Issue: "Application Error" or "502 Bad Gateway"

**Cause:** Application failed to start

**Solutions:**

1. **Check startup logs** in Render dashboard
2. **Verify migrations** ran successfully
3. **Check disk space** (free tier has limits)
4. **Ensure APP_KEY** is set

### Issue: "Database is locked"

**Cause:** SQLite file permissions

**Solutions:**

1. The Dockerfile already sets correct permissions
2. If issue persists, check Render's persistent disk configuration
3. Consider using Render's PostgreSQL for production

## Local Testing

### Test Docker build locally:

```bash
# Build image
docker build -t euroleague-stats:test .

# Run container
docker run -d -p 8080:80 --name test-app euroleague-stats:test

# Check logs
docker logs test-app

# Test application
curl http://localhost:8080

# View in browser
# Open http://localhost:8080

# Cleanup
docker stop test-app && docker rm test-app
```

### Test Vite build locally:

```bash
npm run build
```

Check that `public/build/manifest.json` exists and contains references to your CSS/JS files.

## Production Checklist

- [ ] All environment variables set in Render
- [ ] APP_DEBUG is false
- [ ] APP_ENV is production
- [ ] Database path is writable
- [ ] Vite manifest exists after build
- [ ] Apache mod_rewrite enabled (done in Dockerfile)
- [ ] Storage directory is writable (done in Dockerfile)

## File Structure After Build

```
/var/www/html/
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── build/
│       ├── manifest.json  ← Must exist!
│       ├── assets/
│       │   ├── app-[hash].js
│       │   └── app-[hash].css
│       └── ...
├── app/
├── resources/
├── storage/
└── ...
```

## Support

If you encounter issues:

1. Check Render deployment logs
2. Check application logs in Render dashboard
3. Verify environment variables are set
4. Test Docker image locally first

## Notes

- The application uses **Blade views** (not full Inertia SPA)
- Some pages may use Inertia components
- Vite builds happen during Docker build, not runtime
- Apache serves the application on port 80
- SQLite database is stored in `database/database.sqlite`

