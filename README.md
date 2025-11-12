<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Euroleague Stats & Recommendations

A Laravel application for tracking Euroleague basketball statistics and providing player recommendations based on form and defensive matchups.

## Features

- **Game Statistics Tracking**: Fetch and store complete game statistics from the Euroleague API
- **Defense vs Position Analysis**: Analyze how teams defend against different positions (Guards, Forwards, Centers)
- **Form-Based Recommendations**: Visual graph showing players in good form against favorable defensive matchups
- **Player Overview**: Detailed player statistics and performance tracking
- **Team Analysis**: Team statistics and defensive capabilities
- **Real-time Updates**: Fetch latest game data from Euroleague API

## Quick Start

1. **Setup**: See [SETUP.md](SETUP.md) for installation instructions
2. **Fetch Data**: See [API_USAGE_SUMMARY.md](API_USAGE_SUMMARY.md) for quick reference
3. **Full API Guide**: See [API_INTEGRATION.md](API_INTEGRATION.md) for detailed API documentation

## Documentation

- **[SETUP.md](SETUP.md)** - Initial setup and installation
- **[SCHEDULE_INTEGRATION_SUCCESS.md](SCHEDULE_INTEGRATION_SUCCESS.md)** - ✅ Schedule API integration (WORKING!)
- **[API_INTEGRATION_UPDATED.md](API_INTEGRATION_UPDATED.md)** - ⚠️ Why v1 player API doesn't work
- **[API_USAGE_SUMMARY.md](API_USAGE_SUMMARY.md)** - Quick reference
- **[DEFENSE_VS_POSITION.md](DEFENSE_VS_POSITION.md)** - Defense vs Position feature
- **[FORM_RECOMMENDATIONS.md](FORM_RECOMMENDATIONS.md)** - Form-based recommendations
- **[HMR_SETUP.md](HMR_SETUP.md)** - Hot Module Replacement

## Available Commands

### ✅ Schedule Management (NEW - Works Great!)
```bash
# Sync complete schedule from API
php artisan euroleague:sync-schedule

# Shows:
# - All upcoming games
# - Which played games need stats
# - Complete season overview
```

### ✅ Fetch Game Statistics (Recommended)
```bash
# Fetch all games
php artisan euroleague:fetch-stats --start=1 --end=230

# Fetch specific game (from schedule)
php artisan euroleague:fetch-stats --game=231

# Force reload games
php artisan euroleague:fetch-stats --start=1 --end=97 --force
```

### Recommended Workflow
```bash
# Step 1: Sync schedule (see what games exist)
php artisan euroleague:sync-schedule

# Step 2: Fetch stats for specific games it identifies
php artisan euroleague:fetch-stats --game=<CODE>
```

## Pages

- `/games` - List all games
- `/games/{id}` - Game details
- `/teams` - List all teams
- `/teams/{id}` - Team details
- `/players` - List all players
- `/players/{id}` - Player details
- `/stats-vs-position` - Defense vs Position analysis
- `/form-recommendations` - Player recommendations graph

## Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Vue 3 + Inertia.js
- **Charts**: Chart.js
- **Styling**: Tailwind CSS
- **Database**: MySQL

## Position System

Players are categorized into three positions:
- **G** (Guard) - Point Guards, Shooting Guards
- **F** (Forward) - Small Forwards, Power Forwards
- **C** (Center) - Centers

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
