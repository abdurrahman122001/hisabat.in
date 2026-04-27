from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = [
    'items:m.map(ue=>({printer:ue.printer,material:ue.material,width_cm:ue.width_cm,height_cm:ue.height_cm,qty:ue.qty,price_per_m2:ue.price_per_m2}))',
    'k((se==null?void 0:se.db_error)||(se==null?void 0:se.error)||((F=se==null?void 0:se.errors)==null?void 0:F.stock)||((U=se==null?void 0:se.errors)==null?void 0:U.items)||"Xəta baş verdi")'
]
for idx, needle in enumerate(needles, 1):
    i = s.find(needle)
    print(f'--- TARGET {idx} IDX={i} LEN={len(needle)} ---')
    if i != -1:
        print(needle)
        print('--- CONTEXT ---')
        print(s[max(0, i-120):min(len(s), i+len(needle)+120)])
