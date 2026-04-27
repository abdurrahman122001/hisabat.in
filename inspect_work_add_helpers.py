from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
for needle in ['Xt=V=>v.find(F=>F.name===V)||null','Kn=V=>','Gt(','const G=S.useCallback(async()=>{try{const[V,F]=await Promise.all([fetch("/hesabat/api/printers_list.php"),fetch("/hesabat/api/materials_list.php")])']:
    i = s.find(needle)
    print('='*80)
    print('needle:', needle, 'index:', i)
    if i != -1:
        print(s[max(0, i-2500):i+4500])
