import sqlite3

conn = sqlite3.connect('sensor_data.db')

cursor = conn.cursor()

create_table_query = """
CREATE TABLE IF NOT EXIST sensor_data(
   id INTERGER PRIMARY KEY AUTOINCREMENT,
   timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
   value REAL
);
"""
cursor.execute(create_table_query)

conn.commit()
conn.close()