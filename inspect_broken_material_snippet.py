from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needle = 'N.replace(/_/g," ")'
idx = s.find(needle)
print('idx', idx)
print(repr(s[idx:idx+220]))
needle2 = 'W&&W!==R?W:N.replace(/_/g," ")'
idx2 = s.find(needle2)
print('idx2', idx2)
print(repr(s[idx2:idx2+220]))
