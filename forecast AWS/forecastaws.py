# Import library yang diperlukan
import numpy as np
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LogisticRegression
from sklearn import metrics
import matplotlib.pyplot as plt

# Baca data cuaca dari file CSV
# (Pastikan file CSV berisi kolom seperti kelembaban, kecepatan angin, suhu, radiasi_matahari, dll., dan kolom target "curah_hujan")
data = pd.read_csv('data_cuaca.csv')

# Pilih fitur yang akan digunakan untuk prediksi
features = data[['kelembaban', 'kecepatan_angin', 'suhu', 'radiasi_matahari']]

# Pilih target yang ingin diprediksi (curah hujan: 1 atau 0)
target = data['curah_hujan']

# Bagi data menjadi set pelatihan dan pengujian
features_train, features_test, target_train, target_test = train_test_split(features, target, test_size=0.2, random_state=0)

# Inisialisasi model regresi logistik
model = LogisticRegression()

# Latih model menggunakan set pelatihan
model.fit(features_train, target_train)

# Lakukan prediksi pada set pengujian
predictions = model.predict(features_test)

# Evaluasi kinerja model
print('Akurasi:', metrics.accuracy_score(target_test, predictions))
print('Precision:', metrics.precision_score(target_test, predictions))
print('Recall:', metrics.recall_score(target_test, predictions))
print('Confusion Matrix:')
print(metrics.confusion_matrix(target_test, predictions))

# Visualisasi hasil prediksi
plt.scatter(target_test, predictions)
plt.xlabel('Curah Hujan Aktual')
plt.ylabel('Prediksi Curah Hujan')
plt.title('Hubungan Antara Curah Hujan Aktual dan Prediksi Curah Hujan')
plt.show()
