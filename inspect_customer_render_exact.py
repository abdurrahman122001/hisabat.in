from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = [
    'value:e.banner_440_black,onChange:h=>g("banner_440_black",h.target.value)}),o.jsx(z,{label:"Banner 440 GR Black"',
    'value:e.banner_440_black,onChange:h=>g("banner_440_black",h.target.value)})]})]}),o.jsxs("div",{className:"bg-slate-50/50 p-6 rounded-3xl border border-slate-100",children:[o.jsxs("h3",{className:"text-base font-bold text-orange-900 mb-6 flex items-center gap-2",children:[o.jsx("span",{className:"w-2 h-2 rounded-full bg-orange-500"}),"Roland"]})',
    'value:e.banner_440_black,onChange:h=>g("banner_440_black",h.target.value)})',
    'value:e.roland_black_glossy,onChange:h=>g("roland_black_glossy",h.target.value)})',
    'value:e.laser_graw_cut_orch,onChange:h=>g("laser_graw_cut_orch",h.target.value)})'
]
for needle in needles:
    idx = s.find(needle)
    print('NEEDLE', idx)
    if idx!=-1:
        print(s[max(0,idx-400):idx+1200])
        print('\n---\n')
