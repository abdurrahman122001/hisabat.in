from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old = 'Object.keys(he).length>0&&o.jsx("div",{className:"space-y-6",children:Object.entries(he).map(([T,N])=>o.jsxs("div",{className:"bg-slate-50/50 p-6 rounded-3xl border border-slate-100",children:[o.jsxs("h3",{className:"text-base font-bold text-slate-900 mb-6 flex items-center gap-2",children:[o.jsx("span",{className:"w-2 h-2 rounded-full bg-violet-500"}),N.name]}),o.jsx("div",{className:"grid grid-cols-1 md:grid-cols-4 gap-5",children:N.materials.map(L=>o.jsx(z,{label:L.label,placeholder:"Qiymət",inputMode:"decimal",value:((H.prices||{})[T]||{})[L.key]??"",onChange:w=>Xe(T,L.key,w.target.value),error:J[`prices.${T}.${L.key}`]},`${T}-${L.key}`))})]},T))})'
new = 'Object.keys(he).length>0&&o.jsx("div",{className:"space-y-6",children:Object.entries(he).map(([T,N],L)=>o.jsxs("div",{className:"bg-slate-50/50 p-6 rounded-3xl border border-slate-100",children:[o.jsxs("h3",{className:"text-base font-bold text-slate-900 mb-6 flex items-center gap-2",children:[o.jsx("span",{className:["w-2.5","h-2.5","rounded-full",L%3===0?"bg-indigo-500":L%3===1?"bg-orange-500":"bg-teal-500","shadow-sm"].join(" ")}),N.name]}),o.jsx("div",{className:"grid grid-cols-1 md:grid-cols-4 gap-5",children:N.materials.map(w=>o.jsx(z,{label:w.label,placeholder:"Qiymət",inputMode:"decimal",value:((H.prices||{})[T]||{})[w.key]??"",onChange:O=>Xe(T,w.key,O.target.value),error:J[`prices.${T}.${w.key}`]},`${T}-${w.key}`))})]},T))})'

for path in files:
    s = path.read_text(encoding='utf-8')
    if old not in s:
        raise SystemExit(f'target not found in {path}')
    s = s.replace(old, new, 1)
    path.write_text(s, encoding='utf-8')
    print(f'Patched {path}')
