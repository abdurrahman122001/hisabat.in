from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old_state = '[n,r]=S.useState(""),[s,i]=S.useState(""),[a,l]=S.useState(!1),[c,u]=S.useState(""),[d,f]=S.useState([]),p=S.useMemo(()=>d.filter(x=>Number(x.outstanding_debit)>100),[d]),y=S.useCallback((x,j,g="all")=>{'
new_state = '[n,r]=S.useState(""),[s,i]=S.useState(""),[a,l]=S.useState(!1),[c,u]=S.useState(""),[d,f]=S.useState([]),[N,L]=S.useState([]),p=S.useMemo(()=>d.filter(x=>Number(x.outstanding_debit)>100),[d]),y=S.useCallback((x,j,g="all")=>{'

old_fetch_ok = 'if(!g.ok||!(m!=null&&m.ok)){u((m==null?void 0:m.db_error)||(m==null?void 0:m.error)||"Xəta baş verdi"),f([]);return}f(Array.isArray(m.clients)?m.clients:[])}catch{u("Serverə qoşulmaq olmadı"),f([])}finally{l(!1)}}'
new_fetch_ok = 'if(!g.ok||!(m!=null&&m.ok)){u((m==null?void 0:m.db_error)||(m==null?void 0:m.error)||"Xəta baş verdi"),f([]),L([]);return}f(Array.isArray(m.clients)?m.clients:[]),L(Array.isArray(m.advances)?m.advances:[])}catch{u("Serverə qoşulmaq olmadı"),f([]),L([])}finally{l(!1)}}'

anchor = 'p.length>0&&o.jsxs("div",{className:"bg-white rounded-3xl border border-amber-200 shadow-xl shadow-slate-200/40 overflow-hidden",children:[o.jsx("div",{className:"px-6 py-4 bg-amber-50/80 border-b border-amber-200",children:o.jsxs("div",{className:"flex items-center justify-between gap-3",children:[o.jsx("div",{className:"text-sm font-bold text-amber-800",children:"Yüksək borcu olan müştərilər (100 AZN+)"}),o.jsxs(fe,{variant:"secondary",className:"flex items-center gap-2",type:"button",onClick:()=>y("Yüksək borcu olan müştərilər",p,"warning"),children:[o.jsx(dn,{size:18}),"PDF yüklə"]})]})}),o.jsx("div",{className:"overflow-x-auto",children:o.jsxs("table",{className:"w-full text-sm text-left",children:[o.jsx("thead",{className:"text-xs text-amber-700 uppercase bg-amber-50/60 border-b border-amber-100",children:o.jsxs("tr",{children:[o.jsx("th",{className:"px-6 py-4 font-bold",children:"ID"}),o.jsx("th",{className:"px-6 py-4 font-bold",children:"Müştəri adı"}),o.jsx("th",{className:"px-6 py-4 font-bold",children:"Telefon"}),o.jsx("th",{className:"px-6 py-4 font-bold text-rose-700",children:"Borc"})]})}),o.jsx("tbody",{className:"divide-y divide-amber-50",children:p.map((x,j)=>o.jsxs("tr",{className:"bg-white hover:bg-amber-50/40 transition-colors",children:[o.jsx("td",{className:"px-6 py-4 font-semibold text-indigo-600",children:x.client_id}),o.jsx("td",{className:"px-6 py-4 text-slate-900",children:x.name}),o.jsx("td",{className:"px-6 py-4 text-slate-600",children:x.phone}),o.jsx("td",{className:"px-6 py-4 font-bold text-rose-700",children:x.outstanding_debit})]},`warn-${x.client_id||j}`))})]})})]})'
advance_block = 'N.length>0&&o.jsxs("div",{className:"bg-white rounded-3xl border border-emerald-200 shadow-xl shadow-slate-200/40 overflow-hidden",children:[o.jsx("div",{className:"px-6 py-4 bg-emerald-50/80 border-b border-emerald-200",children:o.jsxs("div",{className:"flex items-center justify-between gap-3",children:[o.jsx("div",{className:"text-sm font-bold text-emerald-800",children:"Avansı olan müştərilər"}),o.jsxs(fe,{variant:"secondary",className:"flex items-center gap-2",type:"button",onClick:()=>y("Avansı olan müştərilər",N,"all"),children:[o.jsx(dn,{size:18}),"PDF yüklə"]})]})}),o.jsx("div",{className:"overflow-x-auto",children:o.jsxs("table",{className:"w-full text-sm text-left",children:[o.jsx("thead",{className:"text-xs text-emerald-700 uppercase bg-emerald-50/60 border-b border-emerald-100",children:o.jsxs("tr",{children:[o.jsx("th",{className:"px-6 py-4 font-bold",children:"ID"}),o.jsx("th",{className:"px-6 py-4 font-bold",children:"Müştəri adı"}),o.jsx("th",{className:"px-6 py-4 font-bold",children:"Telefon"}),o.jsx("th",{className:"px-6 py-4 font-bold",children:"Email"}),o.jsx("th",{className:"px-6 py-4 font-bold text-emerald-700",children:"Avans"}),o.jsx("th",{className:"px-6 py-4 font-bold text-right",children:"Ümumi əməliyyat"})]})}),o.jsx("tbody",{className:"divide-y divide-emerald-50",children:N.map((x,j)=>o.jsxs("tr",{className:"bg-white hover:bg-emerald-50/40 transition-colors",children:[o.jsx("td",{className:"px-6 py-4 font-semibold text-indigo-600",children:x.client_id}),o.jsx("td",{className:"px-6 py-4 text-slate-900",children:x.name}),o.jsx("td",{className:"px-6 py-4 text-slate-600",children:x.phone}),o.jsx("td",{className:"px-6 py-4 text-slate-600",children:x.email}),o.jsx("td",{className:"px-6 py-4 font-bold text-emerald-700",children:x.advanced}),o.jsx("td",{className:"px-6 py-4 text-slate-600 text-right",children:x.total_amount})]},`adv-${x.client_id||j}`))})]})})]})'
new_anchor = anchor + ',' + advance_block

for path in files:
    s = path.read_text(encoding='utf-8')
    for old, new, label in [
        (old_state, new_state, 'state'),
        (old_fetch_ok, new_fetch_ok, 'fetch'),
        (anchor, new_anchor, 'advance-block'),
    ]:
        if old not in s:
            raise SystemExit(f'{label} target not found in {path}')
        s = s.replace(old, new, 1)
    path.write_text(s, encoding='utf-8')
    print(f'Patched {path}')
