from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old = 'const J=S.useCallback(async()=>{u(!0),f("");try{const A=new URLSearchParams;n.trim()&&A.set("search",n.trim()),s&&A.set("from",s),a&&A.set("to",a);const M=`/hesabat/api/list_works.php${A.toString()?`?${A.toString()}`:""}`,I=await fetch(M),C=await I.json().catch(()=>null);if(!I.ok||!(C!=null&&C.ok)){f((C==null?void 0:C.db_error)||(C==null?void 0:C.error)||"Xəta baş verdi"),x([]);return}x(Array.isArray(C.works)?C.works:[])}catch{f("Serverə qoşulmaq olmadı"),x([])}finally{u(!1)}},[n,s,a]);S.useEffect(()=>{J()},[J]);'
new = 'const D=S.useRef(0),J=S.useCallback(async()=>{const A=++D.current;u(!0),f("");try{const M=new URLSearchParams;n.trim()&&M.set("search",n.trim()),s&&M.set("from",s),a&&M.set("to",a);const I=`/hesabat/api/list_works.php${M.toString()?`?${M.toString()}`:""}`,C=await fetch(I),L=await C.json().catch(()=>null);if(A!==D.current)return;if(!C.ok||!(L!=null&&L.ok)){f((L==null?void 0:L.db_error)||(L==null?void 0:L.error)||"Xəta baş verdi"),x([]);return}x(Array.isArray(L.works)?L.works:[])}catch{A===D.current&&(f("Serverə qoşulmaq olmadı"),x([]))}finally{A===D.current&&u(!1)}},[n,s,a]);S.useEffect(()=>{J()},[J]);'

for path in files:
    s = path.read_text(encoding='utf-8')
    if old not in s:
        raise SystemExit(f'Old snippet not found in {path}')
    path.write_text(s.replace(old, new, 1), encoding='utf-8')
    print(f'Patched {path}')
