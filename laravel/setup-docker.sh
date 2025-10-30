#!/bin/bash

echo "🚀 Setting up Laravel Photobooth in Docker..."

# Check if .env exists, if not copy from .env.example
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
fi

# Check if database file exists
if [ ! -f database/database.sqlite ]; then
    echo "📦 Creating SQLite database..."
    touch database/database.sqlite
fi

# Build and start containers
echo "🐳 Building Docker containers..."
docker-compose build

echo "🚀 Starting containers..."
docker-compose up -d

# Wait for containers to be ready
echo "⏳ Waiting for containers to start..."
sleep 5

# Generate application key if not set
echo "🔑 Generating application key..."
docker-compose exec app php artisan key:generate

# Run migrations
echo "📊 Running database migrations..."
docker-compose exec app php artisan migrate --force

# Create storage link
echo "🔗 Creating storage symlink..."
docker-compose exec app php artisan storage:link

# Set permissions
echo "🔒 Setting permissions..."
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache

echo "✅ Setup complete!"
echo ""
echo "🌐 Application is running at: http://localhost:8080"
echo ""
echo "📋 Useful commands:"
echo "  docker-compose logs -f          # View logs"
echo "  docker-compose down             # Stop containers"
echo "  docker-compose up -d            # Start containers"
echo "  docker-compose exec app bash    # Access app container shell"
