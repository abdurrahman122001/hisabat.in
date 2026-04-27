from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
print('separate_states', '[qe,Ke]=S.useState("")' in s and '[Ve,Be]=S.useState("")' in s)
print('assign_select_uses_qe', 'value:qe,onChange:T=>{const N=T.target.value,L=p.find' in s)
print('assign_button_uses_q', 'onClick:q,disabled:n||ce.length===0||!qe' in s)
