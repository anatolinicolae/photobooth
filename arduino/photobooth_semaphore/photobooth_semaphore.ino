/*
 * Photobooth Semaphore Controller
 * 
 * This Arduino sketch controls a button and 4 LEDs for a semaphore countdown.
 * When the button is pressed, it sends a "START" command to the Python script
 * via serial communication. The Python script then controls the LED sequence
 * by sending "3", "2", "1", "GO" commands back to the Arduino.
 * 
 * Hardware Setup:
 * - Button connected to pin 2 (with pull-up resistor)
 * - LED 1 (Red) connected to pin 8
 * - LED 2 (Yellow) connected to pin 9
 * - LED 3 (Yellow) connected to pin 10
 * - LED 4 (Green) connected to pin 11
 */

// Pin definitions
const int BUTTON_PIN = 2;
const int LED_PINS[] = {8, 9, 10, 11};  // LED1, LED2, LED3, LED4
const int NUM_LEDS = 4;

// Button state tracking
int lastButtonState = HIGH;  // Pull-up resistor, so HIGH when not pressed
int buttonState = HIGH;
unsigned long lastDebounceTime = 0;
unsigned long debounceDelay = 50;

// Semaphore state
bool semaphoreActive = false;

void setup() {
  // Initialize serial communication at 9600 baud
  Serial.begin(9600);
  
  // Configure button pin with internal pull-up resistor
  pinMode(BUTTON_PIN, INPUT_PULLUP);
  
  // Configure LED pins as outputs
  for (int i = 0; i < NUM_LEDS; i++) {
    pinMode(LED_PINS[i], OUTPUT);
    digitalWrite(LED_PINS[i], LOW);  // Start with all LEDs off
  }
  
  Serial.println("Arduino Semaphore Ready");
}

void loop() {
  // Read button state with debouncing
  int reading = digitalRead(BUTTON_PIN);
  
  if (reading != lastButtonState) {
    lastDebounceTime = millis();
  }
  
  if ((millis() - lastDebounceTime) > debounceDelay) {
    if (reading != buttonState) {
      buttonState = reading;
      
      // Button pressed (LOW because of pull-up) and not already running
      if (buttonState == LOW && !semaphoreActive) {
        Serial.println("START");
        semaphoreActive = true;
      }
    }
  }
  
  lastButtonState = reading;
  
  // Check for commands from Python
  if (Serial.available() > 0) {
    String command = Serial.readStringUntil('\n');
    command.trim();
    
    if (command == "3") {
      // Turn on LED 1 (Red)
      turnOffAllLEDs();
      digitalWrite(LED_PINS[0], HIGH);
      Serial.println("LED 1 ON (3)");
    }
    else if (command == "2") {
      // Turn on LEDs 1 and 2
      turnOffAllLEDs();
      digitalWrite(LED_PINS[0], HIGH);
      digitalWrite(LED_PINS[1], HIGH);
      Serial.println("LEDs 1-2 ON (2)");
    }
    else if (command == "1") {
      // Turn on LEDs 1, 2, and 3
      turnOffAllLEDs();
      digitalWrite(LED_PINS[0], HIGH);
      digitalWrite(LED_PINS[1], HIGH);
      digitalWrite(LED_PINS[2], HIGH);
      Serial.println("LEDs 1-3 ON (1)");
    }
    else if (command == "GO") {
      // Turn on all LEDs (Green GO signal)
      turnOffAllLEDs();
      digitalWrite(LED_PINS[3], HIGH);
      Serial.println("LED 4 ON (GO)");
      delay(1000);  // Keep GO light on for 1 second
      turnOffAllLEDs();
      semaphoreActive = false;  // Reset state
    }
    else if (command == "RESET") {
      // Reset all LEDs
      turnOffAllLEDs();
      semaphoreActive = false;
      Serial.println("RESET");
    }
  }
}

void turnOffAllLEDs() {
  for (int i = 0; i < NUM_LEDS; i++) {
    digitalWrite(LED_PINS[i], LOW);
  }
}
