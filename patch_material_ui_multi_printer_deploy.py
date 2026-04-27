from pathlib import Path
path = Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js')
s = path.read_text(encoding='utf-8')
old_state = '[ce,ee]=S.useState("")'
new_state = '[ce,ee]=S.useState([])'
old_submit = 'body:JSON.stringify({key:R,label:W,category:ce,status:1})'
new_submit = 'body:JSON.stringify({key:R,label:W,categories:ce,category:ce[0]||"",status:1})'
old_reset = 'H(""),J(""),ee(""),l("Material yaradıldı")'
new_reset = 'H(""),J(""),ee([]),l("Material yaradıldı")'
old_ui = 'o.jsxs("div",{className:"grid grid-cols-1 md:grid-cols-4 gap-4",children:[o.jsx(z,{label:"key (məs: banner_matt)",value:R,onChange:T=>H(T.target.value)}),o.jsx(z,{label:"label",value:W,onChange:T=>J(T.target.value)}),o.jsxs("div",{children:[o.jsx("div",{className:"text-sm font-bold text-slate-700 mb-2",children:"Printer seç"}),o.jsxs("select",{className:"w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700",value:ce,onChange:T=>ee(T.target.value),children:[o.jsx("option",{value:"",children:"Printer seç"}),d.filter(T=>Number((T==null?void 0:T.status)??1)===1).map(T=>o.jsx("option",{value:String((T==null?void 0:T.price_key)??"").trim()||String((T==null?void 0:T.name)??"").trim(),children:T.name},T.id))]})]}),o.jsx("div",{className:"flex items-end",children:o.jsxs(fe,{type:"button",className:"w-full flex items-center gap-2",onClick:I,disabled:n,children:[o.jsx(Fs,{size:16}),"Yarat"]})})]})'
new_ui = 'o.jsxs("div",{className:"grid grid-cols-1 md:grid-cols-4 gap-4",children:[o.jsx(z,{label:"key (məs: banner_matt)",value:R,onChange:T=>H(T.target.value)}),o.jsx(z,{label:"label",value:W,onChange:T=>J(T.target.value)}),o.jsxs("div",{className:"md:col-span-2",children:[o.jsx("div",{className:"text-sm font-bold text-slate-700 mb-2",children:"Printer seç"}),o.jsx("div",{className:"rounded-2xl border border-gray-200 bg-white px-4 py-3 max-h-48 overflow-y-auto space-y-2",children:d.filter(T=>Number((T==null?void 0:T.status)??1)===1).map(T=>{const N=String((T==null?void 0:T.price_key)??"").trim()||String((T==null?void 0:T.name)??"").trim();return o.jsxs("label",{className:"flex items-center gap-2 text-sm font-semibold text-slate-700",children:[o.jsx("input",{type:"checkbox",checked:ce.includes(N),onChange:L=>ee(w=>L.target.checked?w.includes(N)?w:[...w,N]:w.filter(O=>O!==N))}),o.jsx("span",{children:T.name})]},T.id)})})]}),o.jsx("div",{className:"flex items-end",children:o.jsxs(fe,{type:"button",className:"w-full flex items-center gap-2",onClick:I,disabled:n||ce.length===0,children:[o.jsx(Fs,{size:16}),"Yarat"]})})]})'
for old, new in [(old_state,new_state),(old_submit,new_submit),(old_reset,new_reset),(old_ui,new_ui)]:
    if old not in s:
        raise SystemExit(f'missing snippet: {old[:80]}')
    s = s.replace(old, new, 1)
path.write_text(s, encoding='utf-8')
print('patched deploy bundle')
