# Hot Module Replacement (HMR) Setup Guide

## ✅ HMR is Now Configured!

Vite HMR (Hot Module Replacement) allows you to see changes instantly without refreshing your browser.

## 🚀 How to Use HMR

### 1. Start the Vite Dev Server

In your terminal, run:

```bash
npm run dev
```

This will start the Vite development server at `http://localhost:5173`

You should see output like:
```
VITE v7.x.x  ready in XXX ms

➜  Local:   http://localhost:5173/
➜  Network: use --host to expose
➜  press h + enter to show help

LARAVEL v11.x.x  plugin v2.x.x

➜  APP_URL: http://localhost
```

### 2. Keep It Running

**Important:** Keep this terminal window open and the dev server running while you develop.

### 3. Start Your Laravel Server

In a **separate terminal**, start your Laravel application:

```bash
php artisan serve
```

Or if using Laragon, just start Apache/nginx as usual.

### 4. Access Your Application

Visit your Laravel application URL (e.g., `http://localhost:8000` or `http://euroleague-stats-recommendation.test`)

## 🎯 What HMR Watches

The Vite dev server will automatically reload when you edit:

- ✅ **Blade files** (`resources/views/**/*.blade.php`)
- ✅ **CSS files** (`resources/css/app.css`)
- ✅ **JavaScript files** (`resources/js/app.js`)
- ✅ **Routes** (`routes/**/*.php`)
- ✅ **Controllers** (`app/Http/Controllers/**/*.php`)

## 💡 How It Works

1. **Edit any file** in the watched directories
2. **Save the file**
3. **Browser automatically refreshes** (or hot-reloads CSS without refresh!)

## 🔧 Configuration Details

### Vite Config (`vite.config.js`)
```javascript
server: {
    hmr: {
        host: 'localhost',
    },
    host: 'localhost',
    port: 5173,
    strictPort: true,
    watch: {
        usePolling: true,  // Important for Windows!
    },
}
```

### Watch Paths
```javascript
refresh: [
    'resources/views/**/*.blade.php',  // All Blade templates
    'routes/**/*.php',                  // All routes
    'app/Http/Controllers/**/*.php',    // All controllers
]
```

## 📝 Tips

### Faster Development Workflow

1. **Open 2 terminals:**
   - Terminal 1: `npm run dev` (Vite HMR)
   - Terminal 2: `php artisan serve` (Laravel)

2. **Edit your code** and watch it reload automatically!

3. **CSS changes** will update without page refresh (instant!)

4. **Blade/PHP changes** will trigger a full page reload

### Windows Users

The `usePolling: true` setting is specifically for Windows to ensure file changes are detected properly.

### Troubleshooting

**HMR not working?**
- Make sure `npm run dev` is running
- Check that port 5173 is not blocked by firewall
- Clear browser cache (Ctrl+Shift+R)
- Check browser console for errors

**Port already in use?**
- Stop other Vite processes
- Or change port in `vite.config.js`

**Slow reload?**
- This is normal on first load
- Subsequent changes should be instant

## 🎨 Development vs Production

### Development (HMR Active)
```bash
npm run dev  # Start dev server with HMR
php artisan serve  # Start Laravel
# Visit http://localhost:8000
```

### Production Build
```bash
npm run build  # Build optimized assets
# Deploy to production
```

## 🌙 Dark Mode Toggle

Your dark mode toggle will work seamlessly with HMR. When you edit styles, the dark mode theme will update instantly!

## ⚡ Benefits

- **No manual refresh needed**
- **Instant CSS updates** (preserves page state)
- **Fast feedback loop**
- **Better development experience**
- **Automatic browser sync**

---

**Ready to go!** Just run `npm run dev` and start coding! 🚀

