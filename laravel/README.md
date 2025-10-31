# Photo Booth - Laravel Image Gallery

A Laravel-based image gallery application with real-time updates using Server-Sent Events (SSE).

## Features

- **Upload Images**: Support for PNG, GIF, and JPG files (up to 100MB)
- **Delete Images**: Remove images by ID
- **List Images**: Display the last 50 uploaded images
- **Real-time Updates**: Automatic UI updates when images are uploaded or deleted using Server-Sent Events
- **Docker Support**: Containerized deployment with Nginx

## Requirements

### Local Development
- PHP 8.3 or higher
- Composer
- SQLite (default) or MySQL/PostgreSQL

### Docker Deployment
- Docker
- Docker Compose

## Installation

### Option 1: Docker (Recommended)

1. Navigate to the Laravel directory:
```bash
cd laravel
```

2. Run the setup script:
```bash
./setup-docker.sh
```

3. Visit `http://localhost:8080` in your browser

The setup script will:
- Create `.env` file if it doesn't exist
- Build Docker containers
- Generate application key
- Run database migrations
- Create storage symlink
- Set proper permissions

**Docker Commands:**
```bash
# View logs
docker-compose logs -f

# Stop containers
docker-compose down

# Start containers
docker-compose up -d

# Access app container shell
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan migrate
```

### Option 2: Local Development

1. Navigate to the Laravel directory:
```bash
cd laravel
```

2. Install dependencies:
```bash
composer install
```

3. The application is already configured with:
   - SQLite database (`database/database.sqlite`)
   - Application key generated
   - Storage link created
   - Migrations run

4. Start the development server:
```bash
php artisan serve
```

5. Visit `http://localhost:8000` in your browser

## User & API Token Management

This application uses Artisan commands to manage users and API tokens instead of a web interface.

### Create a User

```bash
# Interactive mode (recommended)
php artisan user:create

# With options
php artisan user:create --email=admin@example.com --password=secret123 --name="Admin User"

# Docker
docker-compose exec app php artisan user:create
```

### Generate an API Token

```bash
# Interactive mode (recommended)
php artisan token:generate

# With options
php artisan token:generate --email=admin@example.com --name="Arduino Token" --abilities=upload,delete

# Docker
docker-compose exec app php artisan token:generate
```

**Available abilities:** `upload`, `delete` (or both)

**Important:** The token is displayed only once. Save it immediately!

### List API Tokens

```bash
# List tokens for a specific user
php artisan token:list --email=admin@example.com

# List all tokens for all users
php artisan token:list --all

# Docker
docker-compose exec app php artisan token:list --all
```

### Revoke API Tokens

```bash
# Interactive mode - select which token to revoke
php artisan token:revoke --email=admin@example.com

# Revoke a specific token by ID
php artisan token:revoke --id=1

# Revoke all tokens for a user
php artisan token:revoke --email=admin@example.com --all

# Docker
docker-compose exec app php artisan token:revoke --email=admin@example.com
```

## API Endpoints

### API Authentication

Most API endpoints require authentication using API tokens. Include the token in the `Authorization` header as a Bearer token:

```
Authorization: Bearer YOUR_API_TOKEN_HERE
```

### Upload Image
```
POST /api/images
Authorization: Bearer YOUR_API_TOKEN_HERE
Content-Type: multipart/form-data

Parameters:
- image: File (PNG, GIF, or JPG, max 100MB)

Response (201):
{
  "message": "Image uploaded successfully",
  "image": {
    "id": 1,
    "filename": "image.png",
    "path": "images/1234567890_image.png",
    "mime_type": "image/png",
    "size": 12345,
    "created_at": "2025-10-30T22:16:57.000000Z",
    "updated_at": "2025-10-30T22:16:57.000000Z"
  }
}
```

### List Images
```
GET /api/images

Response (200):
{
  "images": [
    {
      "id": 1,
      "filename": "image.png",
      "url": "http://localhost:8000/storage/images/1234567890_image.png",
      "mime_type": "image/png",
      "size": 12345,
      "created_at": "2025-10-30T22:16:57+00:00"
    }
  ]
}
```

**Note:** This endpoint does not require authentication and is publicly accessible.


### Delete Image
```
DELETE /api/images/{id}
Authorization: Bearer YOUR_API_TOKEN_HERE

Response (200):
{
  "message": "Image deleted successfully"
}

Response (404):
{
  "error": "Image not found"
}

Response (401):
{
  "error": "Unauthorized",
  "message": "API token required. Please provide a valid Bearer token in the Authorization header."
}

Response (403):
{
  "error": "Forbidden",
  "message": "This token does not have the required 'delete' permission."
}
```

### Server-Sent Events Stream
```
GET /api/events

Headers:
Content-Type: text/event-stream

Events:
- Image created: {"type": "created", "image": {...}}
- Image deleted: {"type": "deleted", "id": 1}
```

## Frontend

The application includes a simple, responsive web interface at the root URL (`/`) that features:

- Image upload form with drag-and-drop support
- Real-time gallery grid displaying the last 50 images
- Delete buttons for each image
- Automatic updates via Server-Sent Events
- Connection status indicator
- Smooth animations for image additions and removals

## Storage

Images are stored in `storage/app/public/images/` and are accessible via the `/storage` URL path through a symbolic link.

## Database Schema

### Users Table
- `id`: Primary key
- `name`: User name
- `email`: User email (unique)
- `password`: Hashed password
- `created_at`: Timestamp
- `updated_at`: Timestamp

### API Tokens Table
- `id`: Primary key
- `user_id`: Foreign key to users table
- `name`: Token description/name
- `token`: SHA-256 hashed token (64 characters)
- `abilities`: JSON array of permissions (upload, delete, or *)
- `last_used_at`: Last time the token was used
- `expires_at`: Optional expiration timestamp
- `created_at`: Timestamp
- `updated_at`: Timestamp

### Images Table
- `id`: Primary key
- `filename`: Original filename
- `path`: Storage path
- `mime_type`: Image MIME type (image/png, image/gif, or image/jpeg)
- `size`: File size in bytes
- `created_at`: Timestamp
- `updated_at`: Timestamp
- `deleted_at`: Soft delete timestamp (nullable)

## Configuration

The application uses the default Laravel configuration with:
- SQLite database (can be changed in `.env`)
- Local file storage driver
- Cache-based event broadcasting (for SSE)

### Docker Configuration

The Docker setup includes:
- **PHP 8.3-FPM** container with all required extensions
- **Nginx** web server with SSE-optimized configuration
- Persistent volumes for storage and database
- Port 8080 exposed (configurable in `docker-compose.yml`)

**Nginx SSE Configuration:**
- Buffering disabled for real-time streaming
- Extended timeouts (3600s) for long-lived connections
- Proper headers for event streams
- Upload size limit: 100MB

## Development

### Local Development

To reset the database:
```bash
php artisan migrate:fresh
```

To clear the cache:
```bash
php artisan cache:clear
```

### Docker Development

Run artisan commands in Docker:
```bash
docker-compose exec app php artisan migrate:fresh
docker-compose exec app php artisan cache:clear
```

View application logs:
```bash
docker-compose logs -f app
```

## Testing

You can test the API using cURL:

### Setup: Create a user and generate an API token

```bash
# Create a user (interactive)
php artisan user:create
# Or with Docker:
docker-compose exec app php artisan user:create

# Generate an API token (interactive)
php artisan token:generate
# Or with Docker:
docker-compose exec app php artisan token:generate

# Save the token that is displayed - you'll need it for authenticated requests!
```

### Upload an image (requires authentication):

**Important:** Use the `Authorization` header with `Bearer` prefix (NOT in the query string):

```bash
# Local development
curl -X POST http://localhost:8000/api/images \
  -H "Authorization: Bearer YOUR_API_TOKEN_HERE" \
  -F "image=@/path/to/image.png"

# Docker
curl -X POST http://localhost:8080/api/images \
  -H "Authorization: Bearer YOUR_API_TOKEN_HERE" \
  -F "image=@/path/to/image.jpg"

# Example with a real token:
curl -X POST http://localhost:8080/api/images \
  -H "Authorization: Bearer a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6a7b8c9d0e1f2" \
  -F "image=@photo.png"
```

### List images (public):
```bash
curl http://localhost:8080/api/images
```

### Delete an image (requires authentication):
```bash
curl -X DELETE http://localhost:8080/api/images/1 \
  -H "Authorization: Bearer YOUR_API_TOKEN_HERE"
```

### Subscribe to events (public):
```bash
curl -N http://localhost:8080/api/events
```

## Production Deployment

### Docker Production

1. Update `.env` for production:
```bash
APP_ENV=production
APP_DEBUG=false
```

2. Build and deploy:
```bash
docker-compose up -d --build
```

3. For HTTPS, add a reverse proxy (Traefik, Caddy) or modify nginx config

### Traditional Hosting

For traditional hosting (shared hosting, VPS):
- Upload files via FTP/SFTP
- Run `composer install --optimize-autoloader --no-dev`
- Set web root to `/public` directory
- Configure web server for SSE support (disable buffering)

## Notes

- The SSE implementation uses a simple cache-based approach suitable for development
- For production, consider using Laravel Echo with Redis or Pusher for more robust real-time features
- Nginx is configured to handle SSE properly with buffering disabled
- Maximum upload size: 100MB (configurable in both php.ini and nginx.conf)
- The application limits uploads to PNG and GIF files only
- Maximum file size is 10MB (configurable in `ImageController`)
- Only the last 50 images are kept in the frontend display

