from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old = 'd&&o.jsx("div",{className:"rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700",children:d}),o.jsxs("div",{className:"space-y-4",children:['
new = 'd&&o.jsx("div",{className:"rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700",children:d}),o.jsx("div",{className:"rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-bold text-amber-800",children:`DEBUG ne:${ne.length} | v:${v.length} | first:${(ne[0]?.client_name)||(ne[0]?.client_id)||"-"}`}),o.jsxs("div",{className:"space-y-4",children:['

for path in files:
    s = path.read_text(encoding='utf-8')
    if old not in s:
        raise SystemExit(f'Target not found in {path}')
    s = s.replace(old, new, 1)
    path.write_text(s, encoding='utf-8')
    print(f'Patched {path}')
