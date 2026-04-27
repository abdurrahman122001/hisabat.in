from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old_state = 'E=["konica_banner_matt","konica_banner_glossy","konica_vinily_ch","konica_vinily_eu","konica_banner_black_matt","konica_banner_black_glossy","konica_white_banner","konica_white_vinily","konica_backleed","konica_flax","konica_banner_404_white","konica_banner_440_black","roland_banner_matt","roland_banner_glossy","roland_vinily_ch","roland_vinily_eu","roland_black_matt","roland_black_glossy","laser_cut_wood","laser_cut_forex","laser_cut_orch","laser_graw_wood","laser_graw_cut_forex","laser_graw_cut_orch"],k='
new_state = 'E=["konica_banner_matt","konica_banner_glossy","konica_vinily_ch","konica_vinily_eu","konica_banner_black_matt","konica_banner_black_glossy","konica_white_banner","konica_white_vinily","konica_backleed","konica_flax","konica_banner_404_white","konica_banner_440_black","roland_banner_matt","roland_banner_glossy","roland_vinily_ch","roland_vinily_eu","roland_black_matt","roland_black_glossy","laser_cut_wood","laser_cut_forex","laser_cut_orch","laser_graw_wood","laser_graw_cut_forex","laser_graw_cut_orch"],K=S.useMemo(()=>[{name:"Konica",dot:"bg-indigo-500",fields:["konica_banner_matt","konica_banner_glossy","konica_vinily_ch","konica_vinily_eu","konica_banner_black_matt","konica_banner_black_glossy","konica_white_banner","konica_white_vinily","konica_backleed","konica_flax","konica_banner_404_white","konica_banner_440_black"]},{name:"Roland",dot:"bg-orange-500",fields:["roland_banner_matt","roland_banner_glossy","roland_vinily_ch","roland_vinily_eu","roland_black_matt","roland_black_glossy"]},{name:"Laser",dot:"bg-teal-500",fields:["laser_cut_wood","laser_cut_forex","laser_cut_orch","laser_graw_wood","laser_graw_cut_forex","laser_graw_cut_orch"]}],[]),k='

old_render = 'o.jsx("div",{className:"grid grid-cols-1 md:grid-cols-3 gap-5",children:E.map(T=>o.jsx(z,{label:T,inputMode:"decimal",value:H[T]||"",onChange:N=>W({...H,[T]:k(N.target.value)})},T))}),Object.keys(he).length>0&&o.jsx("div",{className:"space-y-6",children:Object.entries(he).map(([T,N])=>o.jsxs("div",{className:"bg-slate-50/50 p-6 rounded-3xl border border-slate-100",children:[o.jsxs("h3",{className:"text-base font-bold text-slate-900 mb-6 flex items-center gap-2",children:[o.jsx("span",{className:"w-2 h-2 rounded-full bg-violet-500"}),N.name]}),o.jsx("div",{className:"grid grid-cols-1 md:grid-cols-4 gap-5",children:N.materials.map(L=>o.jsx(z,{label:L.label,placeholder:"Qiymət",inputMode:"decimal",value:((H.prices||{})[T]||{})[L.key]??"",onChange:w=>Xe(T,L.key,w.target.value),error:J[`prices.${T}.${L.key}`]},`${T}-${L.key}`))})]},T))})'
new_render = 'o.jsx("div",{className:"space-y-6",children:K.map(T=>o.jsxs("div",{className:"bg-slate-50/50 p-6 rounded-3xl border border-slate-100",children:[o.jsxs("h3",{className:"text-base font-bold text-slate-900 mb-6 flex items-center gap-2",children:[o.jsx("span",{className:`w-2 h-2 rounded-full ${T.dot}`}),T.name]}),o.jsx("div",{className:"grid grid-cols-1 md:grid-cols-3 gap-5",children:T.fields.map(N=>o.jsx(z,{label:N,inputMode:"decimal",value:H[N]||"",onChange:L=>W({...H,[N]:k(L.target.value)})},N))})]},T.name))}),Object.keys(he).length>0&&o.jsx("div",{className:"space-y-6",children:Object.entries(he).map(([T,N])=>o.jsxs("div",{className:"bg-slate-50/50 p-6 rounded-3xl border border-slate-100",children:[o.jsxs("h3",{className:"text-base font-bold text-slate-900 mb-6 flex items-center gap-2",children:[o.jsx("span",{className:"w-2 h-2 rounded-full bg-violet-500"}),N.name]}),o.jsx("div",{className:"grid grid-cols-1 md:grid-cols-4 gap-5",children:N.materials.map(L=>o.jsx(z,{label:L.label,placeholder:"Qiymət",inputMode:"decimal",value:((H.prices||{})[T]||{})[L.key]??"",onChange:w=>Xe(T,L.key,w.target.value),error:J[`prices.${T}.${L.key}`]},`${T}-${L.key}`))})]},T))})'

for path in files:
    s = path.read_text(encoding='utf-8')
    for old, new, label in [
        (old_state, new_state, 'state'),
        (old_render, new_render, 'render'),
    ]:
        if old not in s:
            raise SystemExit(f'{label} target not found in {path}')
        s = s.replace(old, new, 1)
    path.write_text(s, encoding='utf-8')
    print(f'Patched {path}')
