import serial
import time
from pymodbus.client.sync import ModbusSerialClient

serial_port = 'dev/ttyUSB0'
baudrate = 9600

client = modbus = modbusSerialClient(method='rtu', port=serial_port,baudrate=baudrate,timeout=1)
client.connect()

try:
  while True:
   response = client.read_holding_registers(0,1, unit=1)
   print("Data: ",response.registers)
   time.sleep(1)

except KeyboardInterrupt:
  print("existing program . . .")
  client.close()
