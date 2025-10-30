# Python Semaphore Controller

This Python application controls the photobooth semaphore countdown sequence via serial communication with the Arduino.

## Features

- Listens for "START" command from Arduino when button is pressed
- Sends countdown sequence: "3", "2", "1", "GO" to control LEDs
- Configurable serial port, baud rate, and countdown timing
- Auto-detection of Arduino serial port on macOS, Windows, and Linux
- Clean error handling and graceful shutdown

## Installation

This project uses `pipenv` for dependency management.

### Install pipenv (if not already installed)

```bash
pip install pipenv
```

### Install project dependencies

```bash
cd python
pipenv install
```

This will create a virtual environment and install:
- `pyserial` - for serial communication with Arduino

## Usage

### Activate the virtual environment

```bash
pipenv shell
```

### Run the controller

**Basic usage** (auto-detects port):
```bash
python semaphore_controller.py
```

**List available serial ports**:
```bash
python semaphore_controller.py --list-ports
```

**Specify custom port**:
```bash
# macOS
python semaphore_controller.py --port /dev/cu.usbmodem14101

# Windows
python semaphore_controller.py --port COM5

# Linux
python semaphore_controller.py --port /dev/ttyUSB0
```

**Customize countdown timing**:
```bash
python semaphore_controller.py --delay 1.5
```

**Full options**:
```bash
python semaphore_controller.py --port /dev/cu.usbmodem14101 --baud 9600 --delay 1.0
```

### Exit the program

Press `Ctrl+C` to stop the controller and disconnect from Arduino.

## Command Line Options

- `--port PORT` - Serial port to use (auto-detected by default)
- `--baud BAUD` - Baud rate (default: 9600)
- `--delay DELAY` - Delay between countdown steps in seconds (default: 1.0)
- `--list-ports` - List available serial ports and exit

## Serial Communication Protocol

### Python ← Arduino
- `START` - Button was pressed, begin countdown

### Python → Arduino
- `3` - Display countdown "3" (LED 1)
- `2` - Display countdown "2" (LEDs 1-2)
- `1` - Display countdown "1" (LEDs 1-3)
- `GO` - Display GO signal (LED 4)
- `RESET` - Turn off all LEDs

Baud Rate: 9600

## Troubleshooting

### "Error connecting to port"

1. Check that Arduino is connected via USB
2. Run with `--list-ports` to see available ports
3. Make sure Arduino sketch is uploaded and running
4. Try specifying the port manually with `--port`

### "Permission denied" on Linux

You may need to add your user to the `dialout` group:
```bash
sudo usermod -a -G dialout $USER
```
Then log out and log back in.

### Arduino not responding

1. Verify Arduino sketch is uploaded
2. Open Arduino IDE Serial Monitor to check if Arduino is sending data
3. Make sure no other program is using the serial port
4. Try unplugging and reconnecting the Arduino

## Development

### Install development dependencies

```bash
pipenv install --dev
```

This installs:
- `pylint` - for code linting

### Run linting

```bash
pipenv run pylint semaphore_controller.py
```
