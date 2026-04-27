from pathlib import Path

paths = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

for path in paths:
    s = path.read_text(encoding='utf-8')

    old_printer = 'o.jsx(z,{label:"Printer adı",value:b,onChange:T=>_(T.target.value)}),o.jsx(z,{label:"price_key (konica/roland/laser və ya boş)",value:E,onChange:T=>k(T.target.value)})'
    new_printer = 'o.jsx(z,{label:"Printer adı",value:b,onChange:T=>{const N=T.target.value,L=N.trim().toLowerCase().replace(/[^a-z0-9]+/g,"_").replace(/^_+|_+$/g,"");_(N),k(L)}}),o.jsx(z,{label:"price_key (konica/roland/laser və ya boş)",value:E,onChange:T=>k(T.target.value)})'
    if old_printer not in s:
        raise SystemExit(f'printer target not found in {path}')
    s = s.replace(old_printer, new_printer, 1)

    old_material = 'o.jsx(z,{label:"key (məs: banner_matt)",value:R,onChange:T=>H(T.target.value)}),o.jsx(z,{label:"label",value:Ve,onChange:T=>Be(T.target.value)}),o.jsx("div",{className:"flex items-end",children:o.jsxs(fe,{type:"button",className:"w-full flex items-center gap-2",onClick:async()=>{const T=await fetch("/hesabat/api/materials_create.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({key:R,label:W,status:1})}),N=await T.json().catch(()=>null);if(!T.ok||!(N!=null&&N.ok)){i((N==null?void 0:N.error)||(N==null?void 0:N.db_error)||"Xəta baş verdi");return}H(""),J(""),l("Material yaradıldı"),await P()},disabled:n||!R,children:[o.jsx(Fs,{size:16}),"Yarat"]})})'
    new_material = 'o.jsx(z,{label:"key (məs: banner_matt)",value:R,onChange:T=>{const N=T.target.value,L=N.replace(/_/g," ").replace(/\\b\\w/g,w=>w.toUpperCase());H(N),J(L)}}),o.jsx(z,{label:"label",value:W,onChange:T=>J(T.target.value)}),o.jsx("div",{className:"flex items-end",children:o.jsxs(fe,{type:"button",className:"w-full flex items-center gap-2",onClick:async()=>{const T=await fetch("/hesabat/api/materials_create.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({key:R,label:W,status:1})}),N=await T.json().catch(()=>null);if(!T.ok||!(N!=null&&N.ok)){i((N==null?void 0:N.error)||(N==null?void 0:N.db_error)||"Xəta baş verdi");return}H(""),J(""),l("Material yaradıldı"),await P()},disabled:n||!R,children:[o.jsx(Fs,{size:16}),"Yarat"]})})'
    if old_material not in s:
        raise SystemExit(f'material target not found in {path}')
    s = s.replace(old_material, new_material, 1)

    path.write_text(s, encoding='utf-8')
    print(f'patched {path}')
