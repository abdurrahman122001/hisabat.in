from pathlib import Path

path = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js')
s = path.read_text(encoding='utf-8')
old = 'Gt=(V,F)=>{var se;if(!a||!V||!F)return"";const U=Xt(V),Y=U!=null&&U.price_key?String(U.price_key):"";if(!Y||Y!=="konica"&&Y!=="roland")return"";const Z=(se=a==null?void 0:a[Y])==null?void 0:se[F];return Z==null||Z===""?"":String(Z)}'
new = 'Gt=(V,F)=>{if(!a||!V||!F)return"";const U=Xt(V),Y=[U!=null&&U.price_key?String(U.price_key):"",String(V),String(V).toLowerCase()].filter(Boolean);for(const Z of Y){const se=a==null?void 0:a[Z],ue=se==null?void 0:se[F];if(!(ue==null||ue===""))return String(ue)}return""}'
count = s.count(old)
print({"count": count})
if count != 1:
    raise SystemExit('Expected exact Gt helper target not found once')
s = s.replace(old, new, 1)
path.write_text(s, encoding='utf-8')
print('patched')
