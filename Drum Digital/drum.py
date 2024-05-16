import RPi.GPIO as GPIO
import pygame
import time

# GPIO pin setup
SENSOR_PINS = [17, 27, 22]  # Example GPIO pins for piezo sensors
DRUM_SOUNDS = ["kick.wav", "snare.wav", "hihat.wav"]  # Corresponding sound files

# Initialize Pygame mixer
pygame.mixer.init()

# Load drum sounds
sounds = [pygame.mixer.Sound(sound) for sound in DRUM_SOUNDS]

# GPIO setup
GPIO.setmode(GPIO.BCM)
for pin in SENSOR_PINS:
    GPIO.setup(pin, GPIO.IN, pull_up_down=GPIO.PUD_DOWN)

def play_sound(channel):
    index = SENSOR_PINS.index(channel)
    sounds[index].play()

# Add event detection for each sensor
for pin in SENSOR_PINS:
    GPIO.add_event_detect(pin, GPIO.RISING, callback=play_sound, bouncetime=200)

print("Drum kit ready. Hit the sensors to play sounds!")

try:
    while True:
        time.sleep(0.1)
except KeyboardInterrupt:
    GPIO.cleanup()
