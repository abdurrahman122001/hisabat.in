from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find('function _k(){')
end = s.find('function Nk(){', start)
chunk = s[start:end]
for token in ['mr=S.useMemo', ',mr=S.useMemo', 'mr=', ',mr=', 'price_per_m2:ue.price_per_m2', 'l(U.prices)', 'ut=async']:
    idx = chunk.find(token)
    print('\nTOKEN', token, 'IDX', idx)
    if idx != -1:
        print(chunk[max(0, idx-5000):min(len(chunk), idx+15000)])
        print('\n' + '='*120 + '\n')
