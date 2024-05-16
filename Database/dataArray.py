import sqlite3
conn = sqlite3.connect('TABEL VOLUME.db')
cur = conn.cursor()
cur.execute("SELECT * FROM table_name LIMIT 1")

rows = cur.fetchone()

row_array = list(rows)
element = row_array[1]
print(element)

cur.close()
conn.close()
