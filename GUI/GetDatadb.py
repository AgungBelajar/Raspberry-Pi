import sqlite3

conn = sqlite3.connect('Tank.db')

cur = conn.cursor()
cur.execute("SELECT *FROM Agung ORDER BY datetime ASC LIMIT 1")

oldest = cur.fetchone()
print(oldest)
     
cur.close()
conn.close()