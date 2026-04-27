from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find('function _k(){')
end = s.find('function Nk(){', start)
chunk = s[start:end]
for token in ['Gt=', ',Gt=', 'Gt=V=>', 'Gt=(V', 'Kn=', ',Kn=', 'Kn=V=>', 'mr=S.useMemo', 'mr=', 'ut=async', 'Yt=async']:
    idx = chunk.find(token)
    print('TOKEN', token, 'IDX', idx)
    if idx != -1:
        print(chunk[max(0, idx-2500):idx+12000])
        print('\n' + '='*120 + '\n')
