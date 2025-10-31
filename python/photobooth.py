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
import cv2
from PIL import Image
from datetime import datetime
import os
import requests
from dotenv import load_dotenv


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
        # Load environment variables
        load_dotenv()
        
        self.port = port
        self.baudrate = baudrate
        self.countdown_delay = countdown_delay
        self.serial_connection = None
        self.running = False
        self.cameras = []
        self.num_cameras = 3
        self.output_dir = "captures"
        
        # API configuration from environment variables
        self.api_endpoint = os.getenv('API_UPLOAD_ENDPOINT')
        self.api_token = os.getenv('API_AUTH_TOKEN')
        self.api_timeout = int(os.getenv('API_TIMEOUT', '30'))
        
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
            
            # Initialize cameras
            self.initialize_cameras()
            
            # Create output directory if it doesn't exist
            if not os.path.exists(self.output_dir):
                os.makedirs(self.output_dir)
                print(f"✓ Created output directory: {self.output_dir}")
            
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
        
        # Release cameras
        self.release_cameras()
    
    def initialize_cameras(self):
        """Initialize all webcams by scanning for available cameras."""
        print(f"\n📷 Scanning for cameras (looking for {self.num_cameras})...")
        self.cameras = []
        
        # Try indices 0-20 to find available cameras
        max_test_index = 20
        available_indices = []
        
        for i in range(max_test_index):
            cap = cv2.VideoCapture(i)
            if cap.isOpened():
                # Verify camera is actually working by trying to read a frame
                ret, _ = cap.read()
                if ret:
                    available_indices.append(i)
                    print(f"  ✓ Found working camera at index {i}")
                    
                    # Keep camera open if we need it
                    if len(self.cameras) < self.num_cameras:
                        # Set resolution (optional)
                        cap.set(cv2.CAP_PROP_FRAME_WIDTH, 1280)
                        cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 720)
                        self.cameras.append(cap)
                    else:
                        cap.release()
                else:
                    cap.release()
            
            # Stop early if we found enough cameras
            if len(self.cameras) >= self.num_cameras:
                break
        
        if len(self.cameras) == 0:
            print("  ⚠️  Warning: No cameras detected!")
        elif len(self.cameras) < self.num_cameras:
            print(f"  ⚠️  Warning: Only found {len(self.cameras)} camera(s), expected {self.num_cameras}")
            print(f"✓ {len(self.cameras)} camera(s) ready (indices: {available_indices[:len(self.cameras)]})\n")
        else:
            print(f"✓ {len(self.cameras)} camera(s) ready (indices: {available_indices[:len(self.cameras)]})\n")
    
    def release_cameras(self):
        """Release all camera resources."""
        for i, cam in enumerate(self.cameras):
            if cam is not None and cam.isOpened():
                cam.release()
                print(f"✓ Released camera {i}")
        self.cameras = []
    
    def capture_images(self):
        """
        Capture images from all cameras.
        
        Returns:
            list: List of captured image file paths
        """
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        captured_files = []
        
        print("\n📸 Capturing images...")
        
        for i, cam in enumerate(self.cameras):
            if cam is not None and cam.isOpened():
                # Capture frame
                ret, frame = cam.read()
                
                if ret:
                    # Save image
                    filename = f"{self.output_dir}/cam{i}_{timestamp}.jpg"
                    cv2.imwrite(filename, frame)
                    captured_files.append(filename)
                    print(f"  ✓ Camera {i}: {filename}")
                else:
                    print(f"  ✗ Camera {i}: Failed to capture")
        
        return captured_files
    
    def create_gif(self, image_files, output_filename=None):
        """
        Create a GIF from captured images.
        
        Args:
            image_files (list): List of image file paths
            output_filename (str): Output GIF filename (optional)
            
        Returns:
            str: Path to created GIF file
        """
        if not image_files:
            print("✗ No images to create GIF")
            return None
        
        if output_filename is None:
            timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
            output_filename = f"{self.output_dir}/photobooth_{timestamp}.gif"
        
        print(f"\n🎬 Creating GIF with {len(image_files)} frames...")
        
        # Load images
        images = []
        for img_file in image_files:
            # Convert BGR to RGB (OpenCV uses BGR, Pillow uses RGB)
            img_bgr = cv2.imread(img_file)
            img_rgb = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2RGB)
            pil_img = Image.fromarray(img_rgb)
            images.append(pil_img)
        
        # Create GIF with 250ms duration per frame
        if images:
            images[0].save(
                output_filename,
                save_all=True,
                append_images=images[1:],
                duration=128,  # 250ms per frame
                loop=0  # Infinite loop
            )
            print(f"✓ GIF saved: {output_filename}\n")
            return output_filename
        
        return None
    
    def upload_gif(self, gif_path):
        """
        Upload GIF to the production API.
        
        Args:
            gif_path (str): Path to the GIF file to upload
            
        Returns:
            dict: API response or None if upload fails
        """
        if not self.api_endpoint:
            print("⚠️  API endpoint not configured. Set API_UPLOAD_ENDPOINT in .env file")
            return None
        
        if not os.path.exists(gif_path):
            print(f"✗ GIF file not found: {gif_path}")
            return None
        
        print(f"\n📤 Uploading GIF to {self.api_endpoint}...")
        
        try:
            # Prepare the file for upload
            with open(gif_path, 'rb') as f:
                files = {'image': (os.path.basename(gif_path), f, 'image/gif')}
                
                # Prepare headers
                headers = {}
                if self.api_token:
                    headers['Authorization'] = f'Bearer {self.api_token}'
                
                # Send POST request
                response = requests.post(
                    self.api_endpoint,
                    files=files,
                    headers=headers,
                    timeout=self.api_timeout
                )
                
                # Check response
                if response.status_code in [200, 201]:
                    print(f"✓ Upload successful! Status: {response.status_code}")
                    try:
                        return response.json()
                    except ValueError:
                        return {"status": "success", "message": response.text}
                else:
                    print(f"✗ Upload failed. Status: {response.status_code}")
                    print(f"  Response: {response.text}")
                    return None
                    
        except requests.exceptions.Timeout:
            print(f"✗ Upload timed out after {self.api_timeout} seconds")
            return None
        except requests.exceptions.ConnectionError:
            print(f"✗ Connection error: Could not reach {self.api_endpoint}")
            return None
        except Exception as e:
            print(f"✗ Upload error: {e}")
            return None
    
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
        
        countdown_steps = ["3", "2", "1"]
        
        # Send countdown steps
        for step in countdown_steps:
            self.send_command(step)
            time.sleep(self.countdown_delay)
        
        # Send GO command
        self.send_command("GO")
        time.sleep(0.5)  # Small delay before capturing
        
        # Capture images from all cameras
        captured_files = self.capture_images()
        
        # Create GIF from captured images
        if captured_files:
            gif_file = self.create_gif(captured_files)
            if gif_file:
                print(f"🎉 Photobooth complete! GIF: {gif_file}")
                
                # Upload GIF to API
                upload_result = self.upload_gif(gif_file)
                if upload_result:
                    print(f"🌐 API Response: {upload_result}")
        
        # Send RESET command to Arduino
        time.sleep(0.5)
        self.send_command("RESET")
        
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
