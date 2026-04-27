from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find('e==="materials"?')
end = s.find('function Lk(){')
print('start', start, 'end', end)
print(s[start-200:end+200])
print('open_paren', s.count('('), 'close_paren', s.count(')'))
print('open_brace', s.count('{'), 'close_brace', s.count('}'))
print('open_bracket', s.count('['), 'close_bracket', s.count(']'))
