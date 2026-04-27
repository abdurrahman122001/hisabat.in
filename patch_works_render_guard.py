from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

insert_old = '},[n,s,a]);S.useEffect(()=>{J()},[J]);const ce=async A=>{if(window.confirm("İşi silmək istədiyindən əminsən?")){' 
insert_new = '},[n,s,a]);S.useEffect(()=>{J()},[J]);const ne=S.useMemo(()=>{const A=n.trim(),M=A.replace(/\\s+/g,"").toLowerCase(),I=/^(?=.*[A-Za-z])(?=.*\\d)[A-Za-z0-9_\\s-]+$/.test(A);return I&&M?v.filter(C=>String((C==null?void 0:C.client_id)??"").replace(/\\s+/g,"").toLowerCase()===M):v},[v,n]);const ce=async A=>{if(window.confirm("İşi silmək istədiyindən əminsən?")){' 

render_old = 'children:v.length===0?o.jsx("tr",{className:"bg-slate-50/30",children:o.jsx("td",{className:"px-4 py-8 text-center text-slate-400 italic",colSpan:E.length+1+(t?1:0),children:"Məlumat yoxdur"})}):v.map((A,M)=>o.jsxs("tr",{className:"bg-white hover:bg-slate-50/50 transition-colors",children:['
render_new = 'children:ne.length===0?o.jsx("tr",{className:"bg-slate-50/30",children:o.jsx("td",{className:"px-4 py-8 text-center text-slate-400 italic",colSpan:E.length+1+(t?1:0),children:"Məlumat yoxdur"})}):ne.map((A,M)=>o.jsxs("tr",{className:"bg-white hover:bg-slate-50/50 transition-colors",children:['

for path in files:
    s = path.read_text(encoding='utf-8')
    if insert_old not in s:
        raise SystemExit(f'Insert target not found in {path}')
    s = s.replace(insert_old, insert_new, 1)
    if render_old not in s:
        raise SystemExit(f'Render target not found in {path}')
    s = s.replace(render_old, render_new, 1)
    path.write_text(s, encoding='utf-8')
    print(f'Patched {path}')
