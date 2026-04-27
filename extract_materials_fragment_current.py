from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find(',e==="materials"?o.jsxs(o.Fragment,{children:[')
end = s.find(']}):null]})}function Lk(){')
print('start', start, 'end', end)
frag = s[start:end]
Path(r'c:\xampp\htdocs\Hesabat\tmp_settings_extracts\materials_fragment_broken.txt').write_text(frag, encoding='utf-8')
print('written', len(frag))
print(frag[:300])
print('---')
print(frag[-300:])
