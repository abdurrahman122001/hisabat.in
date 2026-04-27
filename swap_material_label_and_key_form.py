from pathlib import Path

paths = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old = 'o.jsxs("div",{className:"grid grid-cols-1 md:grid-cols-3 gap-4",children:[o.jsx(z,{label:"key (məs: banner_matt)",value:R,onChange:T=>{const N=T.target.value,L=N.replace(/_/g," ").replace(/\\b\\w/g,w=>w.toUpperCase());H(N),J(L)}}),o.jsx(z,{label:"label",value:W,disabled:!0,readOnly:!0}),o.jsx("div",{className:"flex items-end",children:o.jsxs(fe,{type:"button",className:"w-full flex items-center gap-2",onClick:async()=>{const T=await fetch("/hesabat/api/materials_create.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({key:R,label:W,status:1})}),N=await T.json().catch(()=>null);if(!T.ok||!(N!=null&&N.ok)){i((N==null?void 0:N.error)||(N==null?void 0:N.db_error)||"Xəta baş verdi");return}H(""),J(""),l("Material yaradıldı"),await P()},disabled:n||!R,children:[o.jsx(Fs,{size:16}),"Yarat"]})})]})'

new = 'o.jsxs("div",{className:"grid grid-cols-1 md:grid-cols-3 gap-4",children:[o.jsx(z,{label:"Material adı",value:W,onChange:T=>{const N=T.target.value,L=N.toLowerCase().replace(/[ə]/g,"e").replace(/[ü]/g,"u").replace(/[ö]/g,"o").replace(/[ğ]/g,"g").replace(/[ş]/g,"s").replace(/[ç]/g,"c").replace(/[ı]/g,"i").replace(/[^a-z0-9]+/g,"_").replace(/^_+|_+$/g,"");J(N),H(L)}}),o.jsx(z,{label:"key",value:R,disabled:!0,readOnly:!0}),o.jsx("div",{className:"flex items-end",children:o.jsxs(fe,{type:"button",className:"w-full flex items-center gap-2",onClick:async()=>{const T=await fetch("/hesabat/api/materials_create.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({key:R,label:W,status:1})}),N=await T.json().catch(()=>null);if(!T.ok||!(N!=null&&N.ok)){i((N==null?void 0:N.error)||(N==null?void 0:N.db_error)||"Xəta baş verdi");return}H(""),J(""),l("Material yaradıldı"),await P()},disabled:n||!R||!W,children:[o.jsx(Fs,{size:16}),"Yarat"]})})]})'

for path in paths:
    s = path.read_text(encoding='utf-8')
    if old not in s:
        raise SystemExit(f'target not found in {path}')
    s = s.replace(old, new, 1)
    path.write_text(s, encoding='utf-8')
    print(f'patched {path}')
