from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old_memo = '},[n,s,a]);S.useEffect(()=>{J()},[J]);const ne=S.useMemo(()=>{const A=n.trim(),M=A.replace(/\\s+/g,"").toLowerCase(),I=/^(?=.*[A-Za-z])(?=.*\\d)[A-Za-z0-9_\\s-]+$/.test(A);return I&&M?v.filter(C=>String((C==null?void 0:C.client_id)??"").replace(/\\s+/g,"").toLowerCase()===M):v},[v,n]);const ce=async A=>{if(window.confirm("İşi silmək istədiyindən əminsən?")){' 
new_memo = '},[n,s,a]);S.useEffect(()=>{J()},[J]);const ne=S.useMemo(()=>{const A=n.trim(),M=A.replace(/\\s+/g,"").toLowerCase(),I=/^(?=.*[A-Za-z])(?=.*\\d)[A-Za-z0-9_\\s-]+$/.test(A);return I&&M?v.filter(C=>String((C==null?void 0:C.client_id)??"").replace(/\\s+/g,"").toLowerCase()===M):v},[v,n]),re=S.useMemo(()=>`${n}|${s}|${a}|${ne.map(A=>String(A.op_id??"")).join("|")}`,[ne,n,s,a]);const ce=async A=>{if(window.confirm("İşi silmək istədiyindən əminsən?")){' 

old_table = 'children:o.jsxs("table",{className:"w-full text-sm text-left",children:['
new_table = 'children:o.jsxs("table",{key:re,className:"w-full text-sm text-left",children:['

for path in files:
    s = path.read_text(encoding='utf-8')
    if old_memo not in s:
        raise SystemExit(f'Memo target not found in {path}')
    s = s.replace(old_memo, new_memo, 1)
    if old_table not in s:
        raise SystemExit(f'Table target not found in {path}')
    s = s.replace(old_table, new_table, 1)
    path.write_text(s, encoding='utf-8')
    print(f'Patched {path}')
