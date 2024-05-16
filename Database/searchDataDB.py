import sqlite3
conn = sqlite3.connect('TABEL VOLUME.db')
cur = conn.cursor()
search_value = '100'
cur.execute("SELECT * FROM table_name WHERE Tinggi=?",(search_value,))

rows = cur.fetchone()

row_array = list(rows)
element = row_array[1]
print(element)

cur.close()
conn.close()
