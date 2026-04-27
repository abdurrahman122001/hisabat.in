from pathlib import Path

path = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js')
s = path.read_text(encoding='utf-8')
start = s.find('function jk(){')
end = s.find('function _k(){')
chunk = s[start:end]

tokens = [
    'S.useEffect(()=>{j()},[j]);',
    'const g=(h,b)=>{',
    'x(e)<3&&(b.prices="Ən azı 3 məhsula qiymət yazılmalıdır")',
    'fetch("/hesabat/api/create_client.php"',
    'JSON.stringify(e)',
    'o.jsx("div",{className:"pt-4 flex justify-end"',
]

for token in tokens:
    i = chunk.find(token)
    print('=' * 80)
    print(token)
    print('INDEX', i)
    if i != -1:
        print(chunk[max(0, i - 700):i + 2200])
