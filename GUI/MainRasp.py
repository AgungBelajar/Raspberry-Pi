import os
import RPi.GPIO as GPIO
from flask import Flask, render_template,render_template_string, Response,request,jsonify
import datetime
import sqlite3
import os
import glob
import time
import csv
import webbrowser
import subprocess
import psutil

app=Flask(__name__)

level = 213
#sensor temperature
os.system('modprobe w1-gpio')
os.system('modprobe w1-therm')

#database
def insert_data(temp,d,level):
 conn = sqlite3.connect('Tank.db')
 cur=conn.cursor()
 cur.execute('''CREATE TABLE IF NOT EXISTS Agung (
               id INTEGER PRIMARY KEY,
               temp FLOAT NOT NULL,
               level INTEGER NOT NULL,
               datetime TEXT NOT NULL)''')
 cur.execute("INSERT INTO Agung (level,temp,datetime) VALUES (?,?,?)", (level,temp,d))
 conn.commit()
 conn.close()
 print("Data Inserted Success")
 
def cariData():
    kon = sqlite3.connect('TABEL VOLUME.db')
    kur = kon.cursor()
    searchValue = level
    kur.execute("SELECT * FROM table_name WHERE Tinggi=?",(searchValue,))
    rows = kur.fetchone()
    row_array = list(rows)
    volume = row_array[1]
    kur.close()
    kon.close()
    return volume
    
    
@app.route('/export', methods=['GET','POST']) 
def export_data():
 if request.method =='POST':
    # Connect to SQLite database
    conn = sqlite3.connect('Tank.db')
    cur = conn.cursor()
    # Execute a query to fetch data from the database
    cur.execute("SELECT * FROM Agung")
    # Fetch all rows
    rows = cur.fetchall()
    # Write data to CSV file
    with open('users.csv', 'w', newline='') as csvfile:
      csvwriter = csv.writer(csvfile)
      #Write header
      csvwriter.writerow([i[0] for i in cur.description])
      # Write rows
      csvwriter.writerows(rows)
    # Close the connection
    return jsonfy({"message": "Data exported to users.csv"})
 elif request.method =='GET':
        pass
 conn.close()
 messagebox.showinfo("Success", "Data exported successfully")


base_dir = '/sys/bus/w1/devices/'
device_folder = glob.glob(base_dir +'28*')[0]
device_file = device_folder + '/w1_slave'

temp_c=[]
now=datetime.datetime.now()
timeString=now.strftime("%Y-%m-%d %H:%M:%S")
templateData={
    'time':timeString,
    'data':temp_c,
}
def firefox_running():
    for proc in psutil.process_iter(['name']):
        if proc.info['name'] == 'firefox':
            return True
    return False
    
def open_firefox(url):
    if not firefox_running():
      command = ['/usr/bin/firefox',url]
      subprocess.Popen(command)
    
def read_temp_raw():
   f= open(device_file,'r')
   lines = f.readlines()
   f.close()
   return lines

def get_data():
   now=datetime.datetime.now()
   timeString=now.strftime("%Y-%m-%d %H:%M")
   level = 20
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
   values = level, temp_c, timeString
   print(values)
   insert_data(level,temp_c,timeString)
   return temp_c
    
@app.route('/')
def index():
    now=datetime.datetime.now()
    timeString=now.strftime("%Y-%m-%d %H:%M")
    temp_c=get_data()
    Volume=cariData()
    templateData={
        'title':'Storage Tank Based IoT',
        'time':timeString,
        'data':temp_c,
        'level': level,
        'Volume':Volume
            
    }
    #return render_template('rpi_index.html',**templateData)
    return render_template('rpi3b_webcontroller.html',**templateData)           

@app.route('/')
def open_browser():
    js_code = '''
    <script> window.open("http://127.0.0.1:5000",_"blank");
    </script>'''
    return render_template_string(js_code)
    
#@app.route('/<actionid>') 
#def handleRequest(actionid):
#    print("Button pressed : {}".format(export_data))
#    return "OK 200"   
                              
if __name__=='__main__':
    os.system("sudo rm -r  ~/.cache/chromium/Default/Cache/*")
    app.run(debug=True, port=5000, host='0.0.0.0',threaded=True)

url = 'http://127.0.0.1:5000'
    #open_firefox(url)
    #local web server http://192.168.1.200:5000/
    #after Port forwarding Manipulation http://xx.xx.xx.xx:5000/



while True:
   open_firefox(url)
   suhuc = get_data()
   level = 20
   Volume=cariData()
   d = datetime.datetime.now()
   data = insert_sensor_data(suhuc,d)
   cur.execute("INSERT INTO Agung(temp,datetime,level) VALUES (?,?,?)",(suhuc,d,level))
   conn.commit()
   conn.close()
   print ("Insert Success")
   print(data)
   time.sleep(2)
