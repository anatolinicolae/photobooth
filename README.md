# Photobooth Monorepo

A complete photobooth system with Arduino-controlled LED countdown, Python backend, and web applications.

## 📁 Project Structure

```
photobooth/
├── arduino/                    # Arduino LED countdown controller
│   ├── photobooth/
│   │   └── photobooth.ino
│   └── README.md
├── python/                     # Python serial communication backend
│   ├── photobooth.py
│   ├── Pipfile
│   └── README.md
├── laravel/                    # Laravel image gallery with real-time updates
│   ├── app/
│   ├── resources/views/        # Gallery interface
│   ├── routes/                 # API endpoints
│   ├── database/               # Migrations
│   └── README.md
└── README.md                   # This file
```

## 🚀 System Overview

This photobooth system consists of three integrated components:

### 1. **Arduino Project** (`arduino/`)
- Controls a button and 4 LEDs for a visual countdown
- Sends "START" command via serial when button is pressed
- Receives countdown commands ("3", "2", "1", "GO") to control LEDs
- Hardware countdown provides visual feedback

### 2. **Python Backend** (`python/`)
- Long-running script that listens for "START" from Arduino
- Sends countdown sequence to Arduino: "3" → "2" → "1" → "GO"
- Can trigger photo capture and upload to Laravel API
- Uses `pyserial` for serial communication

### 3. **Laravel Image Gallery** (`laravel/`)
- **Image Upload** - PNG and GIF support (up to 10MB)
- **REST API** - Upload, list, and delete endpoints
- **Real-time Updates** - Server-Sent Events (SSE) for live gallery updates
- **Simple Frontend** - Responsive gallery with automatic updates
- **Local Storage** - Images stored in filesystem with database tracking

## 🔧 Quick Start

### Prerequisites

- **Arduino IDE** - for uploading Arduino sketch
- **Python 3.x** - for running the backend
- **PHP 8.2+** - for Laravel application
- **Composer** - PHP dependency manager
- **pipenv** - Python dependency management (`pip install pipenv`)

### Setup Instructions

#### 1. Arduino Setup

```bash
# Navigate to Arduino project
cd arduino

# Open photobooth/photobooth.ino in Arduino IDE
# Connect your Arduino via USB
# Select board and port in Arduino IDE
# Upload the sketch
```

**Wiring:**
- Button: Pin 2 → GND (uses internal pull-up)
- LED 1 (Red): Pin 8 → GND (with 220Ω resistor)
- LED 2 (Yellow): Pin 9 → GND (with 220Ω resistor)
- LED 3 (Yellow): Pin 10 → GND (with 220Ω resistor)
- LED 4 (Green): Pin 11 → GND (with 220Ω resistor)

See `arduino/README.md` for detailed wiring diagram.

#### 2. Python Backend Setup

```bash
# Navigate to Python project
cd python

# Install dependencies
pipenv install

# Activate virtual environment
pipenv shell

# List available serial ports to find your Arduino
python photobooth.py --list-ports

# Run the controller (auto-detects port)
python photobooth.py

# OR specify port manually
python photobooth.py --port /dev/cu.usbmodem14101
```

See `python/README.md` for more options and troubleshooting.

#### 3. Laravel Image Gallery Setup

```bash
# Navigate to Laravel app
cd laravel

# Install dependencies
composer install

# Start the development server
php artisan serve

# Open http://localhost:8000
```

The application is pre-configured with SQLite database and ready to use. See `laravel/README.md` for API documentation and advanced configuration.

## 🎯 How It Works

1. **User presses button** on the Arduino
2. **Arduino sends** `"START"` command via serial to Python
3. **Python script receives** START and begins countdown sequence
4. **Python sends** countdown commands back to Arduino:
   - `"3"` → LED 1 turns on (red)
   - `"2"` → LEDs 1-2 turn on (red + yellow)
   - `"1"` → LEDs 1-3 turn on (red + 2 yellows)
   - `"GO"` → LED 4 turns on (green), then all LEDs turn off
5. **System resets** and waits for next button press

## 🔌 Serial Communication Protocol

**Arduino → Python:**
- `START` - Button pressed, begin countdown

**Python → Arduino:**
- `3` - Display countdown "3" (LED 1 on)
- `2` - Display countdown "2" (LEDs 1-2 on)
- `1` - Display countdown "1" (LEDs 1-3 on)
- `GO` - Display GO signal (LED 4 on, then all off)
- `RESET` - Turn off all LEDs and reset

**Settings:**
- Baud Rate: 9600
- Line Ending: Newline (`\n`)

## 🖼️ Laravel Image Gallery API

### Endpoints

- `POST /api/images` - Upload image (PNG/GIF)
- `GET /api/images` - List last 50 images
- `DELETE /api/images/{id}` - Delete image by ID
- `GET /api/events` - Server-Sent Events stream for real-time updates

### Frontend Features

- Real-time gallery updates via SSE
- Drag-and-drop image upload
- Responsive grid layout
- Connection status indicator
- Smooth animations

See `laravel/README.md` for complete API documentation and usage examples.

## 🛠️ Development

### Testing the System

1. **Upload Arduino sketch** and open Serial Monitor (9600 baud) to verify it's working
2. **Run Python controller** - it should connect and print "Connected to Arduino"
3. **Press the button** - you should see:
   - Arduino Serial Monitor: `START`
   - Python terminal: Countdown sequence
   - LEDs: Sequential countdown pattern
4. **Start Laravel app** - visit `http://localhost:8000` to view the gallery

### Project-Specific Commands

Each project has its own README with detailed instructions:

- **Arduino**: See `arduino/README.md`
- **Python**: See `python/README.md`
- **Laravel**: See `laravel/README.md`

## 🚀 Deployment

### Laravel Deployment

The Laravel application can be deployed to various platforms:

- **Laravel Forge** - Automated Laravel deployment
- **Ploi** - Modern hosting platform
- **DigitalOcean App Platform** - Easy deployment
- **AWS/Heroku** - Traditional cloud platforms

**Build for production:**
```bash
cd laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

See `laravel/README.md` for detailed deployment configuration.

## 📝 Future Enhancements

- [x] Laravel image gallery with real-time updates
- [x] REST API for image management
- [x] Server-Sent Events for live updates
- [ ] Python integration to upload photos after countdown
- [ ] Camera capture triggered by Arduino countdown
- [ ] Live countdown display synchronized with LEDs
- [ ] Photo filters and effects
- [ ] Print queue management
- [ ] Social media sharing
- [ ] Multiple language support

## 🐛 Troubleshooting

### Arduino not connecting

- Check USB connection
- Verify correct port in Arduino IDE
- Ensure no other program is using the serial port
- Try unplugging and reconnecting

### Python can't find serial port

```bash
# List ports
python photobooth.py --list-ports

# Use the correct port
python photobooth.py --port /dev/cu.usbmodem14101
```

### LEDs not lighting up

- Check wiring connections
- Verify LED polarity (longer leg = anode/+)
- Test LEDs with a simple Arduino blink sketch
- Check resistor values (220Ω recommended)

### Laravel issues

- Ensure PHP 8.2+ is installed: `php -v`
- Check storage permissions: `chmod -R 775 storage bootstrap/cache`
- Clear cache: `php artisan cache:clear`
- Re-create storage link: `php artisan storage:link`

## 📄 License

This project is open source. Feel free to use and modify as needed.

## 🙏 Credits

Built with:
- [Arduino](https://www.arduino.cc/)
- [Python](https://www.python.org/) & [PySerial](https://pyserial.readthedocs.io/)
- [Laravel](https://laravel.com/)
- [PHP](https://www.php.net/)
