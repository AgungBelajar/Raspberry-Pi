import sqlite3
import csv

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
    
    # Write header
    csvwriter.writerow([i[0] for i in cur.description])
    
    # Write rows
    csvwriter.writerows(rows)

# Close the connection
conn.close()
