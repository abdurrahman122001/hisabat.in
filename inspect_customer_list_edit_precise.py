from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = [
    'P=async T=>{ce({}),f(""),y("");try{const N=await fetch(`/hesabat/api/get_client.php?client_id=${encodeURIComponent(T.client_id)}`)',
    'W({client_id:String(w.client_id||T.client_id)',
    'update_client.php',
    'Müştərini yükləmək olmadı',
    'Müştəri məlumatını yükləmək olmadı',
    'Müştərini yenilə',
    'Yadda saxla',
]
for needle in needles:
    i = s.find(needle)
    print('='*100)
    print('needle:', needle)
    print('index:', i)
    if i != -1:
        print(s[max(0, i-3000):i+12000])
