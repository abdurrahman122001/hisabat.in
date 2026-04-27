from pathlib import Path

paths = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old = 'ue=S.useMemo(()=>{const h={},b=A=>String(A??"").trim().toLowerCase(),_=new Map,W=new Set(["konica","roland","laser"]);for(const A of N){const V=b(A==null?void 0:A.price_key),F=b(A==null?void 0:A.name),U=V||F;if(!U)continue;V&&_.set(V,U),F&&_.set(F,U)}for(const A of N){const V=b(A==null?void 0:A.price_key),F=b(A==null?void 0:A.name),U=V||F;if(!U||W.has(U))continue;const Y=ne[U]||ne[F]||ne[V]||new Set,Z=w.filter(q=>{const we=b(q==null?void 0:q.category),bi=_.get(we)||we,Qi=String((q==null?void 0:q.key)??"");return bi===U&&!Y.has(Qi)});Z.length>0&&(h[U]={name:String((A==null?void 0:A.name)??U),materials:Z,tone:F==="konica"||U==="konica"?"indigo":F==="roland"||U==="roland"?"orange":F==="laser"||U==="laser"?"teal":String((A==null?void 0:A.name)??U).length%3===0?"indigo":String((A==null?void 0:A.name)??U).length%3===1?"orange":"teal"})}return h},[N,w,ne])'
new = 'ue=S.useMemo(()=>{const h={},b=A=>String(A??"").trim().toLowerCase(),_=new Map,W=new Set(["konica","roland","laser"]);for(const A of N){const V=b(A==null?void 0:A.price_key),F=b(A==null?void 0:A.name),U=V||F;if(!U)continue;V&&_.set(V,U),F&&_.set(F,U)}for(const A of N){const V=b(A==null?void 0:A.price_key),F=b(A==null?void 0:A.name),U=V||F;if(!U)continue;const Y=ne[U]||ne[F]||ne[V]||new Set,Z=w.filter(q=>{const we=b(q==null?void 0:q.category),bi=_.get(we)||we,Qi=String((q==null?void 0:q.key)??"");return bi===U&&(!W.has(U)||!Y.has(Qi))});Z.length>0&&(h[U]={name:String((A==null?void 0:A.name)??U),materials:Z,tone:F==="konica"||U==="konica"?"indigo":F==="roland"||U==="roland"?"orange":F==="laser"||U==="laser"?"teal":String((A==null?void 0:A.name)??U).length%3===0?"indigo":String((A==null?void 0:A.name)??U).length%3===1?"orange":"teal"})}return h},[N,w,ne])'

for path in paths:
    s = path.read_text(encoding='utf-8')
    if old not in s:
        raise SystemExit(f'target not found in {path}')
    s = s.replace(old, new, 1)
    path.write_text(s, encoding='utf-8')
    print(f'patched {path}')
