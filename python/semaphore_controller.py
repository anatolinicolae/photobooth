#!/usr/bin/env python3
"""
Photobooth Semaphore Controller

This script manages the LED semaphore countdown sequence for the photobooth.
It listens for a "START" command from the Arduino via serial communication,
then sends back the countdown sequence: "3", "2", "1", "GO".

Usage:
    python semaphore_controller.py [--port PORT] [--baud BAUD]

Arguments:
    --port PORT    Serial port (default: /dev/cu.usbserial on macOS, COM3 on Windows)
    --baud BAUD    Baud rate (default: 9600)
"""

import serial
import time
import sys
import argparse
import platform


class SemaphoreController:
    """Controls the photobooth semaphore sequence via serial communication."""
    
    def __init__(self, port, baudrate=9600, countdown_delay=1.0):
        """
        Initialize the semaphore controller.
        
        Args:
            port (str): Serial port name
            baudrate (int): Serial communication baud rate
            countdown_delay (float): Delay between countdown steps in seconds
        """
        self.port = port
        self.baudrate = baudrate
        self.countdown_delay = countdown_delay
        self.serial_connection = None
        self.running = False
        
    def connect(self):
        """Establish serial connection to Arduino."""
        try:
            self.serial_connection = serial.Serial(
                self.port,
                self.baudrate,
                timeout=1
            )
            # Wait for Arduino to reset after serial connection
            time.sleep(2)
            print(f"✓ Connected to Arduino on {self.port} at {self.baudrate} baud")
            return True
        except serial.SerialException as e:
            print(f"✗ Error connecting to {self.port}: {e}")
            return False
    
    def disconnect(self):
        """Close serial connection."""
        if self.serial_connection and self.serial_connection.is_open:
            self.send_command("RESET")
            self.serial_connection.close()
            print("\n✓ Disconnected from Arduino")
    
    def send_command(self, command):
        """
        Send a command to the Arduino.
        
        Args:
            command (str): Command to send (e.g., "3", "2", "1", "GO", "RESET")
        """
        if self.serial_connection and self.serial_connection.is_open:
            message = f"{command}\n"
            self.serial_connection.write(message.encode())
            print(f"→ Sent: {command}")
    
    def read_response(self):
        """
        Read and return a line from the serial connection.
        
        Returns:
            str: The received message, or None if no data available
        """
        if self.serial_connection and self.serial_connection.in_waiting > 0:
            try:
                response = self.serial_connection.readline().decode('utf-8').strip()
                if response:
                    print(f"← Received: {response}")
                    return response
            except UnicodeDecodeError:
                pass
        return None
    
    def run_countdown_sequence(self):
        """Execute the 3-2-1-GO countdown sequence."""
        print("\n🚀 Starting countdown sequence!")
        
        countdown_steps = ["3", "2", "1", "GO"]
        
        for step in countdown_steps:
            self.send_command(step)
            time.sleep(self.countdown_delay)
        
        print("✓ Countdown complete!\n")
    
    def run(self):
        """Main loop - listen for START commands and execute countdown."""
        self.running = True
        print("\n👂 Listening for START command from Arduino...")
        print("   (Press Ctrl+C to exit)\n")
        
        try:
            while self.running:
                response = self.read_response()
                
                if response == "START":
                    print("\n🔘 Button pressed!")
                    self.run_countdown_sequence()
                    print("👂 Listening for START command from Arduino...\n")
                
                time.sleep(0.1)  # Small delay to prevent CPU spinning
                
        except KeyboardInterrupt:
            print("\n\n⏹  Stopping semaphore controller...")
            self.running = False


def get_default_port():
    """Determine the default serial port based on the operating system."""
    system = platform.system()
    
    if system == "Darwin":  # macOS
        # Common Arduino ports on macOS
        import glob
        ports = glob.glob('/dev/cu.usbserial*') + glob.glob('/dev/cu.usbmodem*')
        if ports:
            return ports[0]
        return "/dev/cu.usbserial"
    elif system == "Windows":
        return "COM3"
    else:  # Linux
        return "/dev/ttyUSB0"


def main():
    """Main entry point for the semaphore controller."""
    parser = argparse.ArgumentParser(
        description="Photobooth Semaphore Controller - Controls LED countdown via Arduino"
    )
    parser.add_argument(
        "--port",
        type=str,
        default=None,
        help="Serial port (e.g., /dev/cu.usbserial, COM3, /dev/ttyUSB0)"
    )
    parser.add_argument(
        "--baud",
        type=int,
        default=9600,
        help="Baud rate (default: 9600)"
    )
    parser.add_argument(
        "--delay",
        type=float,
        default=1.0,
        help="Delay between countdown steps in seconds (default: 1.0)"
    )
    parser.add_argument(
        "--list-ports",
        action="store_true",
        help="List available serial ports and exit"
    )
    
    args = parser.parse_args()
    
    # List ports if requested
    if args.list_ports:
        try:
            from serial.tools import list_ports
            ports = list_ports.comports()
            print("\nAvailable serial ports:")
            for port in ports:
                print(f"  - {port.device}: {port.description}")
            if not ports:
                print("  No serial ports found")
        except ImportError:
            print("Error: pyserial not installed. Run: pipenv install")
        return
    
    # Determine port
    port = args.port if args.port else get_default_port()
    
    print("=" * 60)
    print("  PHOTOBOOTH SEMAPHORE CONTROLLER")
    print("=" * 60)
    print(f"\nConfiguration:")
    print(f"  Port: {port}")
    print(f"  Baud Rate: {args.baud}")
    print(f"  Countdown Delay: {args.delay}s\n")
    
    # Create and run controller
    controller = SemaphoreController(port, args.baud, args.delay)
    
    if controller.connect():
        try:
            controller.run()
        finally:
            controller.disconnect()
    else:
        print("\nTroubleshooting:")
        print("  1. Check that Arduino is connected via USB")
        print("  2. Verify the correct port with --list-ports")
        print("  3. Ensure Arduino sketch is uploaded")
        print("  4. Try a different port with --port /dev/cu.usbmodem14101 (or similar)")
        sys.exit(1)


if __name__ == "__main__":
    main()
