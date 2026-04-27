from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old = 'if(A!==D.current)return;if(!C.ok||!(L!=null&&L.ok)){f((L==null?void 0:L.db_error)||(L==null?void 0:L.error)||"Xəta baş verdi"),x([]);return}x(Array.isArray(L.works)?L.works:[])}catch{A===D.current&&(f("Serverə qoşulmaq olmadı"),x([]))}finally{A===D.current&&u(!1)}},[n,s,a]);'
new = 'if(A!==D.current)return;if(!C.ok||!(L!=null&&L.ok)){f((L==null?void 0:L.db_error)||(L==null?void 0:L.error)||"Xəta baş verdi"),x([]);return}const Z=n.trim(),Q=Z.replace(/\\s+/g,"").toLowerCase(),X=/^(?=.*[A-Za-z])(?=.*\\d)[A-Za-z0-9_\\s-]+$/.test(Z),Y=Array.isArray(L.works)?L.works:[];x(X&&Q?Y.filter(q=>String((q==null?void 0:q.client_id)??"").replace(/\\s+/g,"").toLowerCase()===Q):Y)}catch{A===D.current&&(f("Serverə qoşulmaq olmadı"),x([]))}finally{A===D.current&&u(!1)}},[n,s,a]);'

for path in files:
    s = path.read_text(encoding='utf-8')
    if old not in s:
        raise SystemExit(f'Old snippet not found in {path}')
    path.write_text(s.replace(old, new, 1), encoding='utf-8')
    print(f'Patched {path}')
