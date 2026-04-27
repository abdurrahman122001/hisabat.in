from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needle = 'items:m.map(ue=>({printer:ue.printer,material:ue.material,width_cm:ue.width_cm,height_cm:ue.height_cm,qty:ue.qty,price_per_m2:ue.price_per_m2}))'
i = s.find(needle)
print('IDX', i)
if i != -1:
    print(repr(s[i-120:i+len(needle)+120]))
needle2 = 'k((se==null?void 0:se.db_error)||(se==null?void 0:se.error)||((F=se==null?void 0:se.errors)==null?void 0:F.stock)||((U=se==null?void 0:se.errors)==null?void 0:U.items)||"Xəta baş verdi")'
i2 = s.find(needle2)
print('IDX2', i2)
if i2 != -1:
    print(repr(s[i2-120:i2+len(needle2)+120]))
