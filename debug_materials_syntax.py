from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find(',e==="materials"?')
end = s.find('function Lk(){')
frag = s[start:end]
print('START', start, 'END', end, 'LEN', len(frag))
print('HEAD:\n', frag[:500])
print('\nTAIL:\n', frag[-500:])
for token in ['null]})}function Lk(){', ']}):null]})}function Lk(){', ']}):null,e===', ']}):null]})}']:
    print(token, s.find(token, start, end+50))
