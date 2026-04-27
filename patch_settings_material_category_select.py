from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old = 'o.jsxs("div",{className:"grid grid-cols-1 md:grid-cols-4 gap-4",children:[o.jsx(z,{label:"key (məs: banner_matt)",value:R,onChange:T=>H(T.target.value)}),o.jsx(z,{label:"label",value:W,onChange:T=>J(T.target.value)}),o.jsx(z,{label:"category (konica/roland/laser və ya printer adı)",value:ce,onChange:T=>ee(T.target.value)}),o.jsx("div",{className:"flex items-end",children:o.jsxs(fe,{type:"button",className:"w-full flex items-center gap-2",onClick:I,disabled:n,children:[o.jsx(Fs,{size:16}),"Yarat"]})})]})'
new = 'o.jsxs("div",{className:"grid grid-cols-1 md:grid-cols-4 gap-4",children:[o.jsx(z,{label:"key (məs: banner_matt)",value:R,onChange:T=>H(T.target.value)}),o.jsx(z,{label:"label",value:W,onChange:T=>J(T.target.value)}),o.jsxs("div",{children:[o.jsx("div",{className:"text-sm font-bold text-slate-700 mb-2",children:"Printer seç"}),o.jsxs("select",{className:"w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700",value:ce,onChange:T=>ee(T.target.value),children:[o.jsx("option",{value:"",children:"Printer seç"}),d.filter(T=>Number((T==null?void 0:T.status)??1)===1).map(T=>o.jsx("option",{value:String((T==null?void 0:T.price_key)??"").trim()||String((T==null?void 0:T.name)??"").trim(),children:T.name},T.id))]})]}),o.jsx("div",{className:"flex items-end",children:o.jsxs(fe,{type:"button",className:"w-full flex items-center gap-2",onClick:I,disabled:n,children:[o.jsx(Fs,{size:16}),"Yarat"]})})]})'

for path in files:
    s = path.read_text(encoding='utf-8')
    if old not in s:
        raise SystemExit(f'target not found in {path}')
    s = s.replace(old, new, 1)
    path.write_text(s, encoding='utf-8')
    print(f'Patched {path}')
