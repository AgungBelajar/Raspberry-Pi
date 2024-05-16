import sqlite3
from tkinter import *
from tkinter import messagebox

# Function to create a new SQLite database
def create_database():
    conn = sqlite3.connect('example.db')
    conn.close()
    messagebox.showinfo("Success", "Database created successfully!")

# Function to create a table in the database
def create_table():
    conn = sqlite3.connect('example.db')
    cur = conn.cursor()
    cur.execute('''CREATE TABLE IF NOT EXISTS users (
                   id INTEGER PRIMARY KEY,
                   name TEXT NOT NULL,
                   email TEXT NOT NULL)''')
    conn.commit()
    conn.close()
    messagebox.showinfo("Success", "Table created successfully!")

# Function to insert data into the table
def insert_data(name, email):
    conn = sqlite3.connect('example.db')
    cur = conn.cursor()
    cur.execute("INSERT INTO users (name, email) VALUES (?, ?)", (name, email))
    conn.commit()
    conn.close()
    messagebox.showinfo("Success", "Data inserted successfully!")

# Function to display all data from the table
def display_data():
    conn = sqlite3.connect('example.db')
    cur = conn.cursor()
    cur.execute("SELECT * FROM users")
    rows = cur.fetchall()
    conn.close()
    for row in rows:
        print(row)

# GUI code
root = Tk()
root.title("SQLite3 GUI")

# Create buttons
create_db_btn = Button(root, text="Create Database", command=create_database)
create_db_btn.pack()

create_table_btn = Button(root, text="Create Table", command=create_table)
create_table_btn.pack()

insert_data_btn = Button(root, text="Insert Data", command=lambda: insert_data("John Doe", "john@example.com"))
insert_data_btn.pack()

display_data_btn = Button(root, text="Display Data", command=display_data)
display_data_btn.pack()

root.mainloop()
