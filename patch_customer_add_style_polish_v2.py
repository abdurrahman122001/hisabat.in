from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old_logic = 'tone:k==="konica"||R==="konica"?{title:"text-indigo-900",dot:"bg-indigo-500"}:k==="roland"||R==="roland"?{title:"text-orange-900",dot:"bg-orange-500"}:k==="laser"||R==="laser"?{title:"text-teal-900",dot:"bg-teal-500"}:{title:"text-slate-900",dot:"bg-slate-500"}'
new_logic = 'tone:k==="konica"||R==="konica"?{title:"text-indigo-900",dot:"bg-indigo-500"}:k==="roland"||R==="roland"?{title:"text-orange-900",dot:"bg-orange-500"}:k==="laser"||R==="laser"?{title:"text-teal-900",dot:"bg-teal-500"}:((String((_==null?void 0:_.name)??R).length)%5===0?{title:"text-violet-900",dot:"bg-violet-500"}:(String((_==null?void 0:_.name)??R).length)%5===1?{title:"text-cyan-900",dot:"bg-cyan-500"}:(String((_==null?void 0:_.name)??R).length)%5===2?{title:"text-emerald-900",dot:"bg-emerald-500"}:(String((_==null?void 0:_.name)??R).length)%5===3?{title:"text-fuchsia-900",dot:"bg-fuchsia-500"}:{title:"text-amber-900",dot:"bg-amber-500"})'

for path in files:
    s = path.read_text(encoding='utf-8')
    if old_logic not in s:
        raise SystemExit(f'logic target not found in {path}')
    s = s.replace(old_logic, new_logic, 1)
    path.write_text(s, encoding='utf-8')
    print(f'Patched {path}')
