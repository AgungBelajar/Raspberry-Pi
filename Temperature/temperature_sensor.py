import sqlite3
import datetime
import os
import glob
import time

os.system('modprobe w1-gpio')
os.system('modprobe w1-therm')

conn = sqlite3.connect('sensordb')
cursor=conn.cursor()
cursor.execute('''CREATE TABLE IF NOT EXISTS sensordb (
               id INTEGER PRIMARY KEY AUTOINCREMENT,
               datetime DATETIME DEFAULT CURRENT_TIMESTAMP,
               temp FLOAT)''')

base_dir = '/sys/bus/w1/devices/'
device_folder = glob.glob(base_dir +'28*')[0]
device_file = device_folder + '/w1_slave'

def waktu():
 current_datetime = datetime.datetime.now()
 d = current_datetime.strftime("%Y-%m-%d %H:%M:%S")
 print(d)
 return d

def read_temp_raw():
   f= open(device_file,'r')
   lines = f.readlines()
   f.close()
   return lines

def read_temp():
   lines = read_temp_raw()
   while lines[0].strip()[-3:] != 'YES':
     time.sleep(0.2)
     lines = read_temp_raw()
   equals_pos =lines[1].find('t=')
   if equals_pos != -1:
     temp_string = lines[1][equals_pos+2:]
     temp_c = float(temp_string)/1000.0
     temp_f = temp_c*9.0/5.0+32.0
     print("Temp C : ", temp_c, " Temp F : ", temp_f)
     return temp_c

def insert_sensor_data(temp,d):
 cursor.execute('''INSERT INTO sensordb (temp,datetime) VALUES (?,?)''', (temp,d))
 conn.commit()

while True:
   suhuc = read_temp()
   d = waktu()
   data = insert_sensor_data(suhuc,d)
   print(data)
   time.sleep(2)
