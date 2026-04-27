from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find('e==="materials"?')
end = s.find(']}):null]})}function Lk(){')
print('start', start, 'end', end)
print(s[start:start+400])
print('---END SNIP---')
print(s[end-400:end+40])
