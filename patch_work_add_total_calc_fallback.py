from pathlib import Path

path = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js')
s = path.read_text(encoding='utf-8')
old = 'Z=parseFloat(String(V.price_per_m2).replace(",","."))||0;return F<=0||U<=0||Y<=0||Z<=0?0:F/100*(U/100)*Y*Z'
new = 'Z=parseFloat(String(V.price_per_m2||Gt(V.printer,V.material)).replace(",","."))||0;return F<=0||U<=0||Y<=0||Z<=0?0:F/100*(U/100)*Y*Z'
count = s.count(old)
print({"count": count})
if count != 1:
    raise SystemExit('Expected total calc target not found exactly once')
s = s.replace(old, new, 1)
path.write_text(s, encoding='utf-8')
print('patched')
