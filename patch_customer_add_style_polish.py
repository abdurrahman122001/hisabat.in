from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old_logic = 'ue=S.useMemo(()=>{const h={},b=A=>String(A??"").trim().toLowerCase();for(const _ of N){const E=b(_==null?void 0:_.price_key),k=b(_==null?void 0:_.name),R=E||k;if(!R)continue;const H=new Set([R,k,E]),W=ne[R]||ne[k]||new Set,J=w.filter(V=>{const Z=b(V==null?void 0:V.category),Y=String((V==null?void 0:V.key)??"");return H.has(Z)&&!W.has(Y)});J.length>0&&(h[R]={name:String((_==null?void 0:_.name)??R),materials:J})}return h},[N,w,ne]);'
new_logic = 'ue=S.useMemo(()=>{const h={},b=A=>String(A??"").trim().toLowerCase();for(const _ of N){const E=b(_==null?void 0:_.price_key),k=b(_==null?void 0:_.name),R=E||k;if(!R)continue;const H=new Set([R,k,E]),W=ne[R]||ne[k]||new Set,J=w.filter(V=>{const Z=b(V==null?void 0:V.category),Y=String((V==null?void 0:V.key)??"");return H.has(Z)&&!W.has(Y)});J.length>0&&(h[R]={name:String((_==null?void 0:_.name)??R),materials:J,tone:k==="konica"||R==="konica"?{title:"text-indigo-900",dot:"bg-indigo-500"}:k==="roland"||R==="roland"?{title:"text-orange-900",dot:"bg-orange-500"}:k==="laser"||R==="laser"?{title:"text-teal-900",dot:"bg-teal-500"}:{title:"text-slate-900",dot:"bg-slate-500"}})}return h},[N,w,ne]);'

old_render = 'Object.keys(ue).length>0&&o.jsx("div",{className:"space-y-6",children:Object.entries(ue).map(([h,b])=>o.jsxs("div",{className:"bg-slate-50/50 p-6 rounded-3xl border border-slate-100",children:[o.jsxs("h3",{className:"text-base font-bold text-slate-900 mb-6 flex items-center gap-2",children:[o.jsx("span",{className:"w-2 h-2 rounded-full bg-violet-500"}),b.name]}),o.jsx("div",{className:"grid grid-cols-1 md:grid-cols-4 gap-5",children:b.materials.map(_=>o.jsx(z,{label:_.label,placeholder:"Qiymət",inputMode:"decimal",value:((e.prices||{})[h]||{})[_.key]??"",onChange:E=>pe(h,_.key,E.target.value)},`${h}-${_.key}`))})]},h))})'
new_render = 'Object.keys(ue).length>0&&o.jsx("div",{className:"space-y-6",children:Object.entries(ue).map(([h,b])=>o.jsxs("div",{className:"bg-slate-50/50 p-6 rounded-3xl border border-slate-100",children:[o.jsxs("h3",{className:`text-base font-bold ${b.tone.title} mb-6 flex items-center gap-2`,children:[o.jsx("span",{className:`w-2 h-2 rounded-full ${b.tone.dot}`}),b.name==="Laser"?"Laser Cutter":b.name]}),o.jsx("div",{className:"grid grid-cols-1 md:grid-cols-4 gap-5",children:b.materials.map(_=>o.jsx(z,{label:String(_.label??"").replace(/[_-]+/g," ").replace(/\b\w/g,E=>E.toUpperCase()),placeholder:"Qiymət",inputMode:"decimal",value:((e.prices||{})[h]||{})[_.key]??"",onChange:E=>pe(h,_.key,E.target.value)},`${h}-${_.key}`))})]},h))})'

for path in files:
    s = path.read_text(encoding='utf-8')
    if old_logic not in s:
        raise SystemExit(f'logic target not found in {path}')
    if old_render not in s:
        raise SystemExit(f'render target not found in {path}')
    s = s.replace(old_logic, new_logic, 1)
    s = s.replace(old_render, new_render, 1)
    path.write_text(s, encoding='utf-8')
    print(f'Patched {path}')
