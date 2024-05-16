import requests
import jq

url="http://srs-ssms.com/post-wl-data.php"

r = requests.post('http://example.com/index.php', params={'q': 'raspberry pi request'})

if r.status_code != 200:
  print ("Error:", r.status_code)

data = r.json()
example = data["idwl" == 1]["d" == timestamp]
print(example)