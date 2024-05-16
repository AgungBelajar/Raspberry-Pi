import pandas as pd
import sqlite3

df = pd.read_excel('Data Massa Jenis PKO 3.xls')
conn = sqlite3.connect('Massa Jenis PKO 3.db')
cur=conn.cursor()
df.to_sql('table_name', conn, if_exists='replace', index=False)
conn.commit()
cur.close()
conn.close()
