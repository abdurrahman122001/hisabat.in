from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old = 'ue=S.useMemo(()=>{const h={};for(const b of N){const _=(String((b==null?void 0:b.price_key)??"").trim().toLowerCase()||String((b==null?void 0:b.name)??"").trim().toLowerCase());if(!_)continue;const E=w.filter(k=>{const R=String((k==null?void 0:k.category)??"").trim().toLowerCase(),H=String((k==null?void 0:k.key)??"");return R===_&&!(ne[_]&&ne[_].has(H))});E.length>0&&(h[_]={name:String((b==null?void 0:b.name)??_),materials:E})}return h},[N,w,ne]);'
new = 'ue=S.useMemo(()=>{const h={},b=A=>String(A??"").trim().toLowerCase();for(const _ of N){const E=b(_==null?void 0:_.price_key),k=b(_==null?void 0:_.name),R=E||k;if(!R)continue;const H=new Set([R,k,E]),W=ne[R]||ne[k]||new Set,J=w.filter(V=>{const Z=b(V==null?void 0:V.category),Y=String((V==null?void 0:V.key)??"");return H.has(Z)&&!W.has(Y)});J.length>0&&(h[R]={name:String((_==null?void 0:_.name)??R),materials:J})}return h},[N,w,ne]);'

for path in files:
    s = path.read_text(encoding='utf-8')
    if old not in s:
        raise SystemExit(f'target not found in {path}')
    s = s.replace(old, new, 1)
    path.write_text(s, encoding='utf-8')
    print(f'Patched {path}')
