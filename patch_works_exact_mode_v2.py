from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old_memo = 'const ne=S.useMemo(()=>{const A=n.trim(),M=A.replace(/\\s+/g,"").toLowerCase(),I=/^(?=.*[A-Za-z])(?=.*\\d)[A-Za-z0-9_\\s-]+$/.test(A);return I&&M?v.filter(C=>String((C==null?void 0:C.client_id)??"").replace(/\\s+/g,"").toLowerCase()===M):v},[v,n]),re=S.useMemo(()=>`${n}|${s}|${a}|${ne.map(A=>String(A.op_id??"")).join("|")}`,[ne,n,s,a]);'
new_memo = 'const ne=S.useMemo(()=>{const A=n.trim(),M=A.replace(/\\s+/g,"").toLowerCase(),I=/^(?=.*[A-Za-z])(?=.*\\d)[A-Za-z0-9_\\s-]+$/.test(A);return I&&M?v.filter(C=>String((C==null?void 0:C.client_id)??"").replace(/\\s+/g,"").toLowerCase()===M):v},[v,n]),se=S.useMemo(()=>{const A=n.trim(),M=A.replace(/\\s+/g,"").toLowerCase(),I=/^(?=.*[A-Za-z])(?=.*\\d)[A-Za-z0-9_\\s-]+$/.test(A);return!!(I&&M)},[n]),re=S.useMemo(()=>`${n}|${s}|${a}|${ne.map(A=>String(A.op_id??"")).join("|")}`,[ne,n,s,a]);'

old_debug = 'o.jsx("div",{className:"rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-bold text-amber-800",children:`DEBUG ne:${ne.length} | v:${v.length} | first:${(ne[0]?.client_name)||(ne[0]?.client_id)||"-"}`}),o.jsxs("div",{className:"space-y-4",children:['
new_debug = 'o.jsx("div",{className:"rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-bold text-amber-800",children:`DEBUG ne:${ne.length} | v:${v.length} | first:${(ne[0]?.client_name)||(ne[0]?.client_id)||"-"}`}),se&&o.jsx("div",{className:"bg-white rounded-3xl border border-gray-100 shadow-xl shadow-slate-200/40 overflow-hidden mt-6",children:o.jsx("div",{className:"overflow-x-auto",children:o.jsxs("table",{className:"w-full text-sm text-left",children:[o.jsx("thead",{className:"text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-gray-100",children:o.jsxs("tr",{children:[o.jsx("th",{className:"px-4 py-4 font-bold whitespace-nowrap",children:"Müştəri"}),o.jsx("th",{className:"px-4 py-4 font-bold whitespace-nowrap",children:"İşin adı"}),o.jsx("th",{className:"px-4 py-4 font-bold whitespace-nowrap",children:"Tarix"}),o.jsx("th",{className:"px-4 py-4 font-bold whitespace-nowrap text-right",children:"Toplam Dəyəri"}),o.jsx("th",{className:"px-4 py-4 font-bold whitespace-nowrap",children:"Əməliyyat ID"})]})}),o.jsx("tbody",{className:"divide-y divide-gray-50",children:ne.length===0?o.jsx("tr",{className:"bg-slate-50/30",children:o.jsx("td",{className:"px-4 py-8 text-center text-slate-400 italic",colSpan:5,children:"Məlumat yoxdur"})}):ne.map((A,M)=>o.jsxs("tr",{className:"bg-white hover:bg-slate-50/50 transition-colors",children:[o.jsx("td",{className:"px-4 py-4 font-semibold text-slate-900 whitespace-nowrap",children:A.client_name||A.client_id}),o.jsx("td",{className:"px-4 py-4 text-slate-600",children:A.work_name}),o.jsx("td",{className:"px-4 py-4 text-slate-600 whitespace-nowrap",children:A.date}),o.jsx("td",{className:"px-4 py-4 text-right text-slate-600",children:A.total_ceiled}),o.jsx("td",{className:"px-4 py-4 font-semibold text-purple-700 whitespace-nowrap",children:A.op_id})]},A.op_id||M))})]})})}),o.jsxs("div",{className:"space-y-4",children:['

old_table = 'o.jsx("div",{className:"bg-white rounded-3xl border border-gray-100 shadow-xl shadow-slate-200/40 overflow-hidden mt-6",children:o.jsx("div",{className:"overflow-x-auto",children:o.jsxs("table",{className:"w-full text-sm text-left",children:['
new_table = 'o.jsx("div",{className:`bg-white rounded-3xl border border-gray-100 shadow-xl shadow-slate-200/40 overflow-hidden mt-6 ${se?"hidden":""}`,children:o.jsx("div",{className:"overflow-x-auto",children:o.jsxs("table",{className:"w-full text-sm text-left",children:['

for path in files:
    s = path.read_text(encoding='utf-8')
    for old, new, label in [
        (old_memo, new_memo, 'memo'),
        (old_debug, new_debug, 'debug'),
        (old_table, new_table, 'table'),
    ]:
        if old not in s:
            raise SystemExit(f'{label} target not found in {path}')
        s = s.replace(old, new, 1)
    path.write_text(s, encoding='utf-8')
    print(f'Patched {path}')
