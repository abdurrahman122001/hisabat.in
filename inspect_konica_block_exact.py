from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
for needle in ['Banner 440 GR White','Banner 440 GR Black','Backleed','children:[o.jsx("span",{className:"w-2 h-2 rounded-full bg-indigo-500"}),"Konica"]']:
    idx = s.find(needle)
    print('\nNEEDLE', needle, idx)
    if idx != -1:
        print(s[max(0, idx-1200):idx+2200])
        print('\n---\n')
