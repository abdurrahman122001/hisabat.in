from pathlib import Path

old = 'o.jsxs(Pt,{className:"p-6",children:[o.jsx("div",{className:"font-bold text-slate-900 mb-4",children:"Yeni material"}),o.jsxs("div",{className:"grid grid-cols-1 md:grid-cols-4 gap-4",children:['
new = 'o.jsxs(Pt,{className:"p-6",children:[o.jsxs("div",{className:"mb-6 rounded-2xl border-2 border-dashed border-violet-200 bg-violet-50/40 px-5 py-4",children:[o.jsx("div",{className:"text-sm font-bold text-violet-700 mb-1",children:"Yeni material yarat"}),o.jsx("div",{className:"text-xs font-medium text-slate-500",children:"Bu hissədə yeni material yarada və sonra printerlərə əlavə edə bilərsiniz."})]}),o.jsx("div",{className:"font-bold text-slate-900 mb-4",children:"Yeni material"}),o.jsxs("div",{className:"grid grid-cols-1 md:grid-cols-4 gap-4",children:['

for rel in [r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js', r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js']:
    path = Path(rel)
    s = path.read_text(encoding='utf-8')
    if old not in s:
        raise SystemExit(f'old snippet not found in {rel}')
    s = s.replace(old, new, 1)
    path.write_text(s, encoding='utf-8')
    print('patched', rel)
