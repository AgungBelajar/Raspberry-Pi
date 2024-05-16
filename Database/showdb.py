import sqlite3
conn = sqlite3.connect('TABEL VOLUME.db')
cur = conn.cursor()
cur.execute("SELECT * FROM table_name LIMIT 10")

rows = cur.fetchall()

for row in rows:
	print(row)

cur.close()
conn.close()
