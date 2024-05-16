from guizero import App, PushButton, Text, Picture, Box
from gpiozero import LED
import sys

def exitApp():
 sys.exit()

def tempRead():
 temp = 26.5
 return temp

suhu = tempRead()



app = App('First Gui', height = 600,width = 800)

gridBox=Box(app,layout="grid")

picture = Picture(gridBox,image="sss.jpg")
picture.grid(row=3,column=3)

TempDisp = PushButton(app,tempRead, text=suhu, align='right', width=15,height = 3)
TempDisp.text_size = 36

exitButton = PushButton(app, exitApp, text="Exit", align="right", width=15,height=3)
exitButton.text_size = 36

app.display()