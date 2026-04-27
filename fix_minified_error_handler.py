from pathlib import Path

path = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js')
s = path.read_text(encoding='utf-8')
old = 'k((se==null?void 0:se.db_error)||(se==null?void 0:se.error)||((F=se==null?void 0:se.errors)==null?void 0:F.stock)||((U=se==null?void 0:se.errors)==null?void 0:U.items)||((Y=se==null?void 0:se.errors)==null?void 0:Y.date)||((Z=se==null?void 0:se.errors)==null?void 0:Z.client_id)||((se==null?void 0:se.errors)==null?void 0:se.errors.work_name)||"Xəta baş verdi")'
new = 'k((se==null?void 0:se.db_error)||(se==null?void 0:se.error)||((F=se==null?void 0:se.errors)==null?void 0:F.stock)||((U=se==null?void 0:se.errors)==null?void 0:U.items)||((se==null?void 0:se.errors)==null?void 0:se.errors.date)||((se==null?void 0:se.errors)==null?void 0:se.errors.client_id)||((se==null?void 0:se.errors)==null?void 0:se.errors.work_name)||"Xəta baş verdi")'
count = s.count(old)
print({'count': count})
if count != 1:
    raise SystemExit('Expected patched error handler not found exactly once')
s = s.replace(old, new, 1)
path.write_text(s, encoding='utf-8')
print('patched')
