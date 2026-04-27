from pathlib import Path

path = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js')
s = path.read_text(encoding='utf-8')
old1 = 'items:m.map(ue=>({printer:ue.printer,material:ue.material,width_cm:ue.width_cm,height_cm:ue.height_cm,qty:ue.qty,price_per_m2:ue.price_per_m2}))'
new1 = 'items:m.map(ue=>({printer:ue.printer,material:ue.material,width_cm:ue.width_cm,height_cm:ue.height_cm,qty:ue.qty,price_per_m2:ue.price_per_m2||Gt(ue.printer,ue.material)}))'
old2 = 'k((se==null?void 0:se.db_error)||(se==null?void 0:se.error)||((F=se==null?void 0:se.errors)==null?void 0:F.stock)||((U=se==null?void 0:se.errors)==null?void 0:U.items)||"Xəta baş verdi")'
new2 = 'k((se==null?void 0:se.db_error)||(se==null?void 0:se.error)||((F=se==null?void 0:se.errors)==null?void 0:F.stock)||((U=se==null?void 0:se.errors)==null?void 0:U.items)||((Y=se==null?void 0:se.errors)==null?void 0:Y.date)||((Z=se==null?void 0:se.errors)==null?void 0:Z.client_id)||((se==null?void 0:se.errors)==null?void 0:se.errors.work_name)||"Xəta baş verdi")'

count1 = s.count(old1)
count2 = s.count(old2)
print({'count1': count1, 'count2': count2})
if count1 != 1 or count2 != 1:
    raise SystemExit('Expected exact unique targets were not found')

s = s.replace(old1, new1, 1)
s = s.replace(old2, new2, 1)
path.write_text(s, encoding='utf-8')
print('patched')
