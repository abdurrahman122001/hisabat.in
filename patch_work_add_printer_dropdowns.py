from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old = 'G=S.useCallback(async()=>{try{const[V,F]=await Promise.all([fetch("/hesabat/api/printers_list.php"),fetch("/hesabat/api/materials_list.php")]),U=await V.json().catch(()=>null),Y=await F.json().catch(()=>null);V.ok&&(U!=null&&U.ok)&&Array.isArray(U.printers)&&x(U.printers.filter(Z=>Number((Z==null?void 0:Z.status)??1)===1)),F.ok&&(Y!=null&&Y.ok)&&Array.isArray(Y.materials)&&g(Y.materials.filter(Z=>Number((Z==null?void 0:Z.status)??1)===1))}catch{}},[])'
new = 'G=S.useCallback(async()=>{try{const[V,F]=await Promise.all([fetch("/hesabat/api/printers_list.php"),fetch("/hesabat/api/materials_list.php")]),U=await V.json().catch(()=>null),Y=await F.json().catch(()=>null),Z=F.ok&&(Y!=null&&Y.ok)&&Array.isArray(Y.materials)?Y.materials.filter(q=>Number((q==null?void 0:q.status)??1)===1):[];if(g(Z),V.ok&&(U!=null&&U.ok)&&Array.isArray(U.printers)){const q=U.printers.filter(we=>Number((we==null?void 0:we.status)??1)===1),we=new Map;q.forEach((bi,Qi)=>{const zi=String((bi==null?void 0:bi.name)??"").trim(),Ii=String((bi==null?void 0:bi.price_key)??"").trim(),nr=zi||Ii;if(!nr)return;we.set(nr.toLowerCase(),{id:(bi==null?void 0:bi.id)??`printer-${Qi}`,name:nr,price_key:Ii,status:1})}),Z.forEach((bi,Qi)=>{const zi=String((bi==null?void 0:bi.category)??"").trim();if(!zi||we.has(zi.toLowerCase()))return;we.set(zi.toLowerCase(),{id:`material-category-${Qi}`,name:zi,price_key:zi,status:1})}),x(Array.from(we.values()))}else{const q=new Map;Z.forEach((we,bi)=>{const Qi=String((we==null?void 0:we.category)??"").trim();if(!Qi||q.has(Qi.toLowerCase()))return;q.set(Qi.toLowerCase(),{id:`material-category-${bi}`,name:Qi,price_key:Qi,status:1})}),x(Array.from(q.values()))}}catch{}},[])'

for path in files:
    s = path.read_text(encoding='utf-8')
    if old not in s:
        raise SystemExit(f'target not found in {path}')
    s = s.replace(old, new, 1)
    path.write_text(s, encoding='utf-8')
    print(f'Patched {path}')
