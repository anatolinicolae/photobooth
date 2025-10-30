# Photobooth Monorepo

A complete photobooth system with Arduino-controlled LED semaphore, Python backend, and React web application.

## 📁 Project Structure

```
photobooth/
├── arduino/                    # Arduino LED semaphore controller
│   ├── photobooth_semaphore/
│   │   └── photobooth_semaphore.ino
│   └── README.md
├── python/                     # Python serial communication backend
│   ├── semaphore_controller.py
│   ├── Pipfile
│   └── README.md
├── webapp/                     # React web application
│   ├── public/
│   ├── src/
│   ├── package.json
│   └── README.md
└── README.md                   # This file
```

## 🚀 System Overview

This photobooth system consists of three integrated components:

### 1. **Arduino Project** (`arduino/`)
- Controls a button and 4 LEDs for a visual countdown
- Sends "START" command via serial when button is pressed
- Receives countdown commands ("3", "2", "1", "GO") to control LEDs
- Hardware semaphore provides visual feedback

### 2. **Python Backend** (`python/`)
- Long-running script that listens for "START" from Arduino
- Sends countdown sequence to Arduino: "3" → "2" → "1" → "GO"
- Uses `pyserial` for serial communication
- Managed with `pipenv` for dependencies

### 3. **React Web App** (`webapp/`)
- Web-based user interface for the photobooth
- Placeholder for future features: camera preview, photo gallery, filters
- Built with React and Create React App

## 🔧 Quick Start

### Prerequisites

- **Arduino IDE** - for uploading Arduino sketch
- **Python 3.x** - for running the backend
- **Node.js & npm** - for running the web app
- **pipenv** - Python dependency management (`pip install pipenv`)

### Setup Instructions

#### 1. Arduino Setup

```bash
# Navigate to Arduino project
cd arduino

# Open photobooth_semaphore/photobooth_semaphore.ino in Arduino IDE
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
python semaphore_controller.py --list-ports

# Run the controller (auto-detects port)
python semaphore_controller.py

# OR specify port manually
python semaphore_controller.py --port /dev/cu.usbmodem14101
```

See `python/README.md` for more options and troubleshooting.

#### 3. Web App Setup (Optional)

```bash
# Navigate to webapp project
cd webapp

# Install dependencies
npm install

# Start development server
npm start

# Opens at http://localhost:3000
```

See `webapp/README.md` for build and deployment instructions.

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

## 🛠️ Development

### Testing the System

1. **Upload Arduino sketch** and open Serial Monitor (9600 baud) to verify it's working
2. **Run Python controller** - it should connect and print "Connected to Arduino"
3. **Press the button** - you should see:
   - Arduino Serial Monitor: `START`
   - Python terminal: Countdown sequence
   - LEDs: Sequential countdown pattern

### Project-Specific Commands

Each project has its own README with detailed instructions:

- **Arduino**: See `arduino/README.md`
- **Python**: See `python/README.md`
- **Web App**: See `webapp/README.md`

## 📝 Future Enhancements

- [ ] Integrate camera capture triggered by countdown
- [ ] Web app displays live countdown synchronized with LEDs
- [ ] WebSocket connection between Python and React app
- [ ] Photo gallery and sharing features
- [ ] Print queue management
- [ ] Custom countdown timing configuration
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
python semaphore_controller.py --list-ports

# Use the correct port
python semaphore_controller.py --port /dev/cu.usbmodem14101
```

### LEDs not lighting up

- Check wiring connections
- Verify LED polarity (longer leg = anode/+)
- Test LEDs with a simple Arduino blink sketch
- Check resistor values (220Ω recommended)

## 📄 License

This project is open source. Feel free to use and modify as needed.

## 🙏 Credits

Built with:
- [Arduino](https://www.arduino.cc/)
- [Python](https://www.python.org/) & [PySerial](https://pyserial.readthedocs.io/)
- [React](https://reactjs.org/)
- [Create React App](https://create-react-app.dev/)
