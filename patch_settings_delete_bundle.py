from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old_funcs = 'C=async(T,N)=>{r(!0),i(""),l("");try{const L=await fetch("/hesabat/api/materials_update.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:T,...N})}),w=await L.json().catch(()=>null);if(!L.ok||!(w!=null&&w.ok)){i((w==null?void 0:w.error)||(w==null?void 0:w.db_error)||"Xəta baş verdi");return}l("Yeniləndi"),await P()}catch{i("Serverə qoşulmaq olmadı")}finally{r(!1)}};return o.jsxs($e.div,'
new_funcs = 'C=async(T,N)=>{r(!0),i(""),l("");try{const L=await fetch("/hesabat/api/materials_update.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:T,...N})}),w=await L.json().catch(()=>null);if(!L.ok||!(w!=null&&w.ok)){i((w==null?void 0:w.error)||(w==null?void 0:w.db_error)||"Xəta baş verdi");return}l("Yeniləndi"),await P()}catch{i("Serverə qoşulmaq olmadı")}finally{r(!1)}},se=async T=>{if(!window.confirm("Printer silinsin?"))return;r(!0),i(""),l("");try{const N=await fetch("/hesabat/api/printers_delete.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:T})}),L=await N.json().catch(()=>null);if(!N.ok||!(L!=null&&L.ok)){i((L==null?void 0:L.error)||(L==null?void 0:L.db_error)||"Xəta baş verdi");return}l("Printer silindi"),await P()}catch{i("Serverə qoşulmaq olmadı")}finally{r(!1)}},re=async T=>{if(!window.confirm("Material silinsin?"))return;r(!0),i(""),l("");try{const N=await fetch("/hesabat/api/materials_delete.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:T})}),L=await N.json().catch(()=>null);if(!N.ok||!(L!=null&&L.ok)){i((L==null?void 0:L.error)||(L==null?void 0:L.db_error)||"Xəta baş verdi");return}l("Material silindi"),await P()}catch{i("Serverə qoşulmaq olmadı")}finally{r(!1)}};return o.jsxs($e.div,'

old_printer_action = 'o.jsx("td",{className:"px-6 py-4 text-right",children:o.jsx(fe,{variant:"secondary",size:"sm",type:"button",onClick:()=>{const N=window.prompt("Yeni ad",T.name);N&&M(T.id,{name:N})},disabled:n,children:"Adı dəyiş"})})'
new_printer_action = 'o.jsx("td",{className:"px-6 py-4 text-right",children:o.jsxs("div",{className:"flex items-center justify-end gap-2",children:[o.jsx(fe,{variant:"secondary",size:"sm",type:"button",onClick:()=>{const N=window.prompt("Yeni ad",T.name);N&&M(T.id,{name:N})},disabled:n,children:"Adı dəyiş"}),o.jsx(fe,{variant:"destructive",size:"sm",type:"button",onClick:()=>se(T.id),disabled:n,children:"Sil"})]})})'

old_material_action = 'o.jsx("td",{className:"px-6 py-4 text-right",children:o.jsx(fe,{variant:"secondary",size:"sm",type:"button",onClick:()=>window.alert("Key və category dəyişmək üçün yeni material yaradın"),children:"Info"})})'
new_material_action = 'o.jsx("td",{className:"px-6 py-4 text-right",children:o.jsxs("div",{className:"flex items-center justify-end gap-2",children:[o.jsx(fe,{variant:"secondary",size:"sm",type:"button",onClick:()=>window.alert("Key və category dəyişmək üçün yeni material yaradın"),children:"Info"}),o.jsx(fe,{variant:"destructive",size:"sm",type:"button",onClick:()=>re(T.id),disabled:n,children:"Sil"})]})})'

for path in files:
    s = path.read_text(encoding='utf-8')
    if old_funcs not in s:
        raise SystemExit(f'functions marker not found in {path}')
    s = s.replace(old_funcs, new_funcs, 1)
    if old_printer_action not in s:
        raise SystemExit(f'printer action marker not found in {path}')
    s = s.replace(old_printer_action, new_printer_action, 1)
    if old_material_action not in s:
        raise SystemExit(f'material action marker not found in {path}')
    s = s.replace(old_material_action, new_material_action, 1)
    path.write_text(s, encoding='utf-8')
    print('patched', path)
