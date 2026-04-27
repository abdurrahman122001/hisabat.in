from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find('function Rk()')
if start == -1:
    start = s.find('/hesabat/api/users_create.php') - 2500
chunk = s[start:start+7000]
pretty = chunk.replace(',const ',',\nconst ').replace('},[]),', '},[]),\n').replace('),const ', '),\nconst ').replace(';return o.jsxs', ';\nreturn o.jsxs')
Path(r'c:\xampp\htdocs\Hesabat\tmp_settings_extracts\settings_component_head.txt').write_text(pretty, encoding='utf-8')
print('done', start)
