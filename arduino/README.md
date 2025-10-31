````markdown
# Arduino Photobooth

This Arduino project controls a button-triggered LED semaphore for a photobooth countdown system.

## Hardware Requirements

- Arduino board (Uno, Nano, or compatible)
- 1 Push button
- 4 LEDs (recommended: 1 red, 2 yellow, 1 green)
- 4 x 220Ω resistors (for LEDs)
- Breadboard and jumper wires

## Wiring Diagram

```
Button:
  - One side to pin 2
  - Other side to GND
  - Internal pull-up resistor is used in code

LEDs (with 220Ω resistors):
  - LED 1 (Red):    Anode -> Pin 8, Cathode -> GND
  - LED 2 (Yellow): Anode -> Pin 9, Cathode -> GND
  - LED 3 (Yellow): Anode -> Pin 10, Cathode -> GND
  - LED 4 (Green):  Anode -> Pin 11, Cathode -> GND
```

## How It Works

1. **Button Press**: When the button is pressed, the Arduino sends "START" via serial communication
2. **Python Control**: The Python script receives the START command and begins the countdown
3. **LED Sequence**: Python sends commands back to Arduino:
   - "3" → LED 1 turns on (red)
   - "2" → LEDs 1-2 turn on (red + yellow)
   - "1" → LEDs 1-3 turn on (red + 2 yellows)
   - "GO" → LED 4 turns on (green), then all LEDs turn off

## Installation

1. Open the Arduino IDE
2. Open `photobooth/photobooth.ino`
3. Select your Arduino board from Tools > Board
4. Select the correct port from Tools > Port
5. Click Upload

## Serial Communication Protocol

### Arduino → Python
- `START` - Button was pressed, begin countdown

### Python → Arduino
- `3` - Display countdown "3"
- `2` - Display countdown "2"
- `1` - Display countdown "1"
- `GO` - Display GO signal
- `RESET` - Turn off all LEDs and reset state

Baud Rate: 9600
