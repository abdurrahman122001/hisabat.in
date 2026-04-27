from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
print('assign_label_uses_Ve', 'o.jsx(z,{label:"label",value:Ve,onChange:T=>Be(T.target.value)})' in s)
